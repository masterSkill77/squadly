<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des membres — {{ $clubName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #374151; }

        .header { padding: 20px 30px; border-bottom: 3px solid #059669; margin-bottom: 20px; }
        .header h1 { font-size: 20px; color: #059669; font-weight: 700; }
        .header p { font-size: 11px; color: #9ca3af; margin-top: 4px; }

        .meta { padding: 0 30px; margin-bottom: 15px; display: flex; }
        .meta span { font-size: 10px; color: #6b7280; margin-right: 20px; }

        table { width: 100%; border-collapse: collapse; margin: 0 30px; width: calc(100% - 60px); }
        thead th {
            background-color: #f0fdf4;
            color: #059669;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 2px solid #d1fae5;
        }
        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 10px;
        }
        tbody tr:nth-child(even) { background-color: #fafafa; }

        .role-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
        }
        .role-president { background-color: #fef3c7; color: #92400e; }
        .role-coach { background-color: #dbeafe; color: #1e40af; }
        .role-membre { background-color: #d1fae5; color: #065f46; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 30px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $clubName }}</h1>
        <p>Liste des membres — {{ $date }} — {{ $total }} membre{{ $total > 1 ? 's' : '' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 18%">Nom</th>
                <th style="width: 20%">Email</th>
                <th style="width: 12%">Téléphone</th>
                <th style="width: 10%">Naissance</th>
                <th style="width: 10%">Rôle</th>
                <th style="width: 25%">Équipes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($members as $i => $member)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight: 600;">{{ $member['last_name'] }} {{ $member['first_name'] }}</td>
                    <td>{{ $member['email'] }}</td>
                    <td>{{ $member['phone'] ?? '—' }}</td>
                    <td>{{ $member['birth_date'] ?? '—' }}</td>
                    <td>
                        @php
                            $roleClass = match($member['role']) {
                                'admin_club' => 'role-president',
                                'coach' => 'role-coach',
                                default => 'role-membre',
                            };
                        @endphp
                        <span class="role-badge {{ $roleClass }}">{{ $member['role_label'] }}</span>
                    </td>
                    <td>{{ $member['teams_label'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Squadly — Manage less. Play more. — Exporté le {{ $date }}
    </div>
</body>
</html>
