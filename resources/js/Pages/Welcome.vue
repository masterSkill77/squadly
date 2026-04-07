<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Landing/Navbar.vue';
import HeroSection from '@/Components/Landing/HeroSection.vue';
import SportsBanner from '@/Components/Landing/SportsBanner.vue';
import FeaturesSection from '@/Components/Landing/FeaturesSection.vue';
import StepsSection from '@/Components/Landing/StepsSection.vue';
import RolesSection from '@/Components/Landing/RolesSection.vue';
import ProfilePreview from '@/Components/Landing/ProfilePreview.vue';
import ShowcaseSection from '@/Components/Landing/ShowcaseSection.vue';
import GallerySection from '@/Components/Landing/GallerySection.vue';
import TestimonialsSection from '@/Components/Landing/TestimonialsSection.vue';
import CtaSection from '@/Components/Landing/CtaSection.vue';
import FooterSection from '@/Components/Landing/FooterSection.vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    clubs: Array,
    stats: Object,
});

const sports = [
    { name: 'Football', emoji: '&#9917;' },
    { name: 'Basketball', emoji: '&#127936;' },
    { name: 'Handball', emoji: '&#129358;' },
    { name: 'Natation', emoji: '&#127946;' },
    { name: 'Rugby', emoji: '&#127945;' },
    { name: 'Volley', emoji: '&#127952;' },
];

const features = [
    { title: 'Gestion des membres', description: 'Profils complets par sport, multi-sections, imports en masse.', icon: 'users' },
    { title: 'Planning & convocations', description: 'Créez entraînements et matchs, envoyez les convocations en temps réel.', icon: 'calendar' },
    { title: 'Feuilles de présence', description: 'Appel digital, taux de présence automatique, historique complet.', icon: 'clipboard' },
    { title: 'Multi-sports', description: 'Football, basket, natation, handball… Un seul outil pour tout le club.', icon: 'globe' },
    { title: 'Compétitions', description: 'Championnats, tournois avec tableau éliminatoire, classements live et calendrier automatique.', icon: 'trophy' },
    { title: 'Rôles & permissions', description: 'Admin, coach, membre : chacun voit ce qui le concerne.', icon: 'shield' },
    { title: 'Documents & communication', description: 'Certificats, licences, annonces. Alertes pour les docs expirés.', icon: 'folder' },
];

const steps = [
    { number: '1', title: 'Créez votre club', description: 'Nom, logo, ville — en 30 secondes.' },
    { number: '2', title: 'Ajoutez vos sports', description: 'Avec les champs adaptés à chaque discipline.' },
    { number: '3', title: 'Créez vos équipes', description: 'Seniors, U17, féminines… organisez librement.' },
    { number: '4', title: 'Invitez vos membres', description: 'Par email, chacun reçoit son accès.' },
];
</script>

<template>
    <Head title="Accueil" />
    <div class="min-h-screen bg-white">
        <Navbar :can-login="canLogin" :can-register="canRegister" />
        <HeroSection :can-register="canRegister" :sports="sports" :stats="stats" />
        <SportsBanner :sports="sports" />
        <FeaturesSection :features="features" />
        <ProfilePreview />
        <StepsSection :steps="steps" />
        <ShowcaseSection :clubs="clubs" />
        <GallerySection />
        <RolesSection />
        <TestimonialsSection />
        <CtaSection :can-register="canRegister" />
        <FooterSection />
    </div>
</template>
