@extends('global.layouts.main')

@section('content')
    <div class="w-full bg-surface dark:bg-surface-dark overflow-hidden">

        <!-- Hero Section -->
        <section class="relative w-full min-h-[40vh] overflow-hidden flex items-center justify-center bg-surface-alt dark:bg-surface-dark-alt">
            <!-- Decorative Pattern -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute inset-0"
                    style="background-image: radial-gradient(circle at 1px 1px, var(--color-primary) 1px, transparent 0); background-size: 40px 40px;">
                </div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <div data-aos="fade-down" data-aos-duration="600"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-radius bg-primary/10 dark:bg-primary/20 text-primary mb-6">
                    <i class="bi bi-shield-check"></i>
                    <span class="text-sm font-medium font-paragraph">Protection des données</span>
                </div>

                <h1 data-aos="fade-up" data-aos-duration="800" data-aos-delay="100"
                    class="font-title text-4xl md:text-5xl lg:text-6xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong mb-6">
                    Politique de Confidentialité
                </h1>

                <p data-aos="fade-up" data-aos-duration="800" data-aos-delay="200"
                    class="text-on-surface/70 dark:text-on-surface-dark/70 text-lg max-w-2xl mx-auto font-paragraph">
                    Dernière mise à jour : <span class="font-semibold text-primary">12 Mars 2026</span>
                </p>

                <div data-aos="fade-up" data-aos-duration="800" data-aos-delay="300"
                    class="flex items-center justify-center gap-2 text-on-surface/60 dark:text-on-surface-dark/60 font-paragraph mt-6">
                    <a href="/" class="hover:text-primary transition">Accueil</a>
                    <i class="bi bi-chevron-right text-xs"></i>
                    <span class="text-primary">Politique de confidentialité</span>
                </div>
            </div>

            <!-- Bottom Wave -->
            <div class="absolute -bottom-0.5 left-0 right-0">
                <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="w-full h-[60px] md:h-[80px]" preserveAspectRatio="none">
                    <path
                        d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                        fill="currentColor" class="text-surface dark:text-surface-dark" />
                </svg>
            </div>
        </section>
    </div>
@endsection