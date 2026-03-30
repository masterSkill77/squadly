<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\MemberProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $club = $request->user()->resolveClub();
        $season = $request->query('season', Document::currentSeason());

        $profiles = $club->memberProfiles()->with('user:id,email')->get();
        $userIds = $profiles->pluck('user_id');

        $documents = Document::where('club_id', $club->id)
            ->where('season', $season)
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $requiredTypes = DocumentType::requiredTypes();

        $members = $profiles->map(function ($profile) use ($documents, $requiredTypes) {
            $memberDocs = $documents->get($profile->user_id, collect());
            $docsByType = $memberDocs->keyBy(fn ($d) => $d->type->value);

            $docsList = [];
            $missingCount = 0;
            $expiredCount = 0;

            foreach ($requiredTypes as $type) {
                $doc = $docsByType->get($type->value);
                if (!$doc) {
                    $missingCount++;
                    $docsList[] = [
                        'id' => null,
                        'type' => $type->value,
                        'type_label' => $type->label(),
                        'status' => 'missing',
                        'status_label' => 'Manquant',
                        'expires_at' => null,
                        'file_url' => null,
                    ];
                } else {
                    if ($doc->status === 'expired') $expiredCount++;
                    $docsList[] = [
                        'id' => $doc->id,
                        'type' => $doc->type->value,
                        'type_label' => $doc->type->label(),
                        'status' => $doc->status,
                        'status_label' => $doc->status_label,
                        'expires_at' => $doc->expires_at?->format('Y-m-d'),
                        'file_url' => $doc->getFirstMediaUrl('document_file'),
                    ];
                }
            }

            // Add "other" type documents
            foreach ($memberDocs->where('type', DocumentType::Other) as $doc) {
                if ($doc->status === 'expired') $expiredCount++;
                $docsList[] = [
                    'id' => $doc->id,
                    'type' => 'other',
                    'type_label' => $doc->custom_label ?? 'Autre',
                    'status' => $doc->status,
                    'status_label' => $doc->status_label,
                    'expires_at' => $doc->expires_at?->format('Y-m-d'),
                    'file_url' => $doc->getFirstMediaUrl('document_file'),
                ];
            }

            return [
                'member_id' => $profile->id,
                'user_id' => $profile->user_id,
                'full_name' => $profile->full_name,
                'first_name' => $profile->first_name,
                'last_name' => $profile->last_name,
                'photo_url' => $profile->getFirstMediaUrl('photo'),
                'documents' => $docsList,
                'missing_count' => $missingCount,
                'expired_count' => $expiredCount,
                'has_issues' => $missingCount > 0 || $expiredCount > 0,
            ];
        });

        $totalExpired = $members->sum('expired_count');
        $totalMissing = $members->sum('missing_count');
        $compliant = $members->where('has_issues', false)->count();

        $seasons = Document::where('club_id', $club->id)
            ->distinct()
            ->pluck('season')
            ->push($season)
            ->unique()
            ->sortDesc()
            ->values();

        return Inertia::render('Documents/Index', [
            'members' => $members,
            'stats' => [
                'total' => $members->count(),
                'compliant' => $compliant,
                'expired' => $totalExpired,
                'missing' => $totalMissing,
            ],
            'documentTypes' => collect(DocumentType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
            'seasons' => $seasons,
            'currentSeason' => $season,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $club = $request->user()->resolveClub();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|in:' . implode(',', array_column(DocumentType::cases(), 'value')),
            'custom_label' => 'nullable|required_if:type,other|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'season' => 'required|string|max:20',
            'expires_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify the user belongs to this club
        abort_if(!MemberProfile::where('user_id', $request->user_id)->where('club_id', $club->id)->exists(), 403);

        // For non-other types, update or create
        $doc = $request->type !== 'other'
            ? Document::updateOrCreate(
                ['user_id' => $request->user_id, 'club_id' => $club->id, 'type' => $request->type, 'season' => $request->season],
                ['expires_at' => $request->expires_at, 'notes' => $request->notes, 'uploaded_by' => $request->user()->id, 'custom_label' => null],
            )
            : Document::create([
                'user_id' => $request->user_id,
                'club_id' => $club->id,
                'type' => $request->type,
                'custom_label' => $request->custom_label,
                'season' => $request->season,
                'expires_at' => $request->expires_at,
                'notes' => $request->notes,
                'uploaded_by' => $request->user()->id,
            ]);

        $doc->addMediaFromRequest('file')->toMediaCollection('document_file');

        return back()->with('success', 'Document enregistré.');
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        abort_if($document->club_id !== $request->user()->resolveClub()?->id, 403);

        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        abort_if($document->club_id !== $request->user()->resolveClub()?->id, 403);

        $media = $document->getFirstMedia('document_file');
        abort_if(!$media, 404);

        return response()->streamDownload(
            fn () => print(file_get_contents($media->getPath())),
            $media->file_name,
            ['Content-Type' => $media->mime_type],
        );
    }
}
