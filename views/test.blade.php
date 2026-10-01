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

        <!-- Content Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-surface dark:bg-surface-dark">
            <div class="max-w-4xl mx-auto">
                
                <!-- Introduction -->
                <div data-aos="fade-up" class="mb-12">
                    <p class="text-lg text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph leading-relaxed">
                        IMPACT-E (« nous », « notre », « nos ») s'engage à protéger la vie privée des visiteurs de notre site web et des personnes qui interagissent avec nos services. Cette politique de confidentialité explique comment nous collectons, utilisons, partageons et protégeons vos informations personnelles conformément au <span class="font-semibold text-primary">Code Pénal Haïtien</span> et aux standards internationaux de protection des données.
                    </p>
                </div>

                <!-- Table des matières -->
                <div data-aos="fade-up" data-aos-delay="100" class="mb-12 p-6 bg-surface-alt dark:bg-surface-dark-alt rounded-radius border border-outline/50 dark:border-outline-dark/50">
                    <h3 class="font-title text-lg font-bold text-on-surface-strong dark:text-on-surface-dark-strong mb-4">
                        Sommaire
                    </h3>
                    <nav class="grid md:grid-cols-2 gap-3">
                        @php
                            $sections = [
                                ['id' => 'collecte', 'title' => '1. Informations que nous collectons'],
                                ['id' => 'utilisation', 'title' => '2. Utilisation de vos informations'],
                                ['id' => 'partage', 'title' => '3. Partage et divulgation'],
                                ['id' => 'conservation', 'title' => '4. Conservation des données'],
                                ['id' => 'securite', 'title' => '5. Sécurité des données'],
                                ['id' => 'droits', 'title' => '6. Vos droits'],
                                ['id' => 'cookies', 'title' => '7. Cookies et technologies similaires'],
                                ['id' => 'modifications', 'title' => '8. Modifications de la politique'],
                                ['id' => 'contact', 'title' => '9. Nous contacter'],
                            ];
                        @endphp
                        @foreach($sections as $section)
                            <a href="#{{ $section['id'] }}" class="flex items-center gap-2 text-sm text-on-surface/70 dark:text-on-surface-dark/70 hover:text-primary transition font-paragraph">
                                <i class="bi bi-arrow-right-short text-primary"></i>
                                {{ $section['title'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <!-- Sections -->
                <div class="space-y-12">
                    
                    <!-- 1. Collecte -->
                    <article id="collecte" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-collection-fill text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                1. Informations que nous collectons
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>Nous collectons les types d'informations suivants :</p>
                            
                            <h4 class="font-title font-semibold text-on-surface-strong dark:text-on-surface-dark-strong mt-6 mb-3">Informations que vous nous fournissez directement</h4>
                            <ul class="list-disc pl-6 space-y-2">
                                <li><strong>Informations de contact</strong> : nom, adresse email, numéro de téléphone, adresse postale</li>
                                <li><strong>Informations de don</strong> : coordonnées bancaires (traitées de manière sécurisée via nos partenaires de paiement)</li>
                                <li><strong>Informations de bénévolat</strong> : compétences, disponibilités, expériences</li>
                                <li><strong>Communications</strong> : contenu des messages que vous nous envoyez</li>
                            </ul>

                            <h4 class="font-title font-semibold text-on-surface-strong dark:text-on-surface-dark-strong mt-6 mb-3">Informations collectées automatiquement</h4>
                            <ul class="list-disc pl-6 space-y-2">
                                <li><strong>Données de navigation</strong> : adresse IP, type de navigateur, pages visitées, temps passé sur le site</li>
                                <li><strong>Données d'appareil</strong> : type d'appareil, système d'exploitation</li>
                                <li><strong>Cookies</strong> : voir section dédiée ci-dessous</li>
                            </ul>
                        </div>
                    </article>

                    <!-- 2. Utilisation -->
                    <article id="utilisation" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-gear-fill text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                2. Utilisation de vos informations
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>Nous utilisons vos informations personnelles aux fins suivantes :</p>
                            
                            <div class="grid md:grid-cols-2 gap-4 mt-6">
                                @php
                                    $uses = [
                                        ['icon' => 'bi-heart-fill', 'text' => 'Traiter vos dons et vous envoyer des reçus fiscaux'],
                                        ['icon' => 'bi-envelope-fill', 'text' => 'Vous tenir informé de nos activités et impact'],
                                        ['icon' => 'bi-people-fill', 'text' => 'Gérer les candidatures de bénévolat'],
                                        ['icon' => 'bi-chat-dots-fill', 'text' => 'Répondre à vos questions et demandes'],
                                        ['icon' => 'bi-graph-up', 'text' => 'Améliorer notre site web et nos services'],
                                        ['icon' => 'bi-shield-fill', 'text' => 'Assurer la sécurité et prévenir la fraude'],
                                    ];
                                @endphp
                                @foreach($uses as $use)
                                    <div class="flex items-start gap-3 p-4 bg-surface-alt dark:bg-surface-dark-alt rounded-radius border border-outline/30 dark:border-outline-dark/30">
                                        <i class="bi {{ $use['icon'] }} text-primary mt-1"></i>
                                        <span class="text-sm">{{ $use['text'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>

                    <!-- 3. Partage -->
                    <article id="partage" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-share-fill text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                3. Partage et divulgation
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>IMPACT-E ne vend pas vos données personnelles. Nous ne partageons vos informations qu'avec :</p>
                            
                            <ul class="list-disc pl-6 space-y-3">
                                <li><strong>Prestataires de services</strong> : hébergement web, traitement des paiements, envoi d'emails (sous contrat de confidentialité strict)</li>
                                <li><strong>Partenaires institutionnels</strong> : uniquement pour des projets conjoints avec votre consentement explicite</li>
                                <li><strong>Obligations légales</strong> : si la loi l'exige ou pour protéger nos droits légaux</li>
                            </ul>

                            <div class="p-4 mt-6 bg-warning/10 border border-warning/30 rounded-radius">
                                <p class="text-sm text-on-surface-strong dark:text-on-surface-dark-strong flex items-start gap-2">
                                    <i class="bi bi-exclamation-triangle-fill text-warning mt-0.5"></i>
                                    <span>Nous ne partageons jamais vos informations de don avec des tiers à des fins de marketing.</span>
                                </p>
                            </div>
                        </div>
                    </article>

                    <!-- 4. Conservation -->
                    <article id="conservation" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-clock-history text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                4. Conservation des données
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>Nous conservons vos données personnelles aussi longtemps que nécessaire pour :</p>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border-collapse mt-6">
                                    <thead>
                                        <tr class="border-b border-outline dark:border-outline-dark">
                                            <th class="text-left py-3 px-4 font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Type de donnée</th>
                                            <th class="text-left py-3 px-4 font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Durée de conservation</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline/50 dark:divide-outline-dark/50">
                                        <tr>
                                            <td class="py-3 px-4">Donateurs actifs</td>
                                            <td class="py-3 px-4">Durée de la relation + 5 ans (obligations fiscales)</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 px-4">Candidatures bénévolat</td>
                                            <td class="py-3 px-4">2 ans après la dernière interaction</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 px-4">Données de navigation</td>
                                            <td class="py-3 px-4">13 mois maximum</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 px-4">Emails et correspondances</td>
                                            <td class="py-3 px-4">3 ans après le dernier contact</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </article>

                    <!-- 5. Sécurité -->
                    <article id="securite" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-lock-fill text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                5. Sécurité des données
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées :</p>
                            
                            <div class="grid md:grid-cols-2 gap-4 mt-6">
                                @php
                                    $security = [
                                        'Chiffrement SSL/TLS pour toutes les transmissions',
                                        'Authentification forte pour l\'accès aux données',
                                        'Sauvegardes régulières et sécurisées',
                                        'Formation de notre personnel à la protection des données',
                                        'Audits de sécurité périodiques',
                                        'Hébergement sur des serveurs sécurisés (AWS/Cloud)',
                                    ];
                                @endphp
                                @foreach($security as $item)
                                    <div class="flex items-center gap-2 text-sm">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span>{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>

                    <!-- 6. Droits -->
                    <article id="droits" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-person-check-fill text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                6. Vos droits
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>Conformément à la législation en vigueur, vous disposez des droits suivants concernant vos données personnelles :</p>
                            
                            <div class="space-y-4 mt-6">
                                @php
                                    $rights = [
                                        ['title' => 'Droit d\'accès', 'desc' => 'Obtenir une copie de vos données personnelles'],
                                        ['title' => 'Droit de rectification', 'desc' => 'Corriger des données inexactes ou incomplètes'],
                                        ['title' => 'Droit à l\'effacement', 'desc' => 'Demander la suppression de vos données (droit à l\'oubli)'],
                                        ['title' => 'Droit d\'opposition', 'desc' => 'Vous opposer au traitement de vos données'],
                                        ['title' => 'Droit à la portabilité', 'desc' => 'Récupérer vos données dans un format structuré'],
                                        ['title' => 'Droit de limitation', 'desc' => 'Restreindre temporairement le traitement'],
                                    ];
                                @endphp
                                @foreach($rights as $right)
                                    <div class="flex items-start gap-4 p-4 bg-surface-alt dark:bg-surface-dark-alt rounded-radius border border-outline/30 dark:border-outline-dark/30">
                                        <div class="w-8 h-8 rounded-radius bg-primary/10 flex items-center justify-center flex-shrink-0">
                                            <i class="bi bi-check-lg text-primary text-sm"></i>
                                        </div>
                                        <div>
                                            <h5 class="font-semibold text-on-surface-strong dark:text-on-surface-dark-strong text-sm mb-1">{{ $right['title'] }}</h5>
                                            <p class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">{{ $right['desc'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <p class="mt-6">Pour exercer ces droits, contactez-nous à <a href="mailto:privacy@impact-e.org" class="text-primary hover:underline">privacy@impact-e.org</a>. Nous répondrons dans un délai maximum d'un mois.</p>
                        </div>
                    </article>

                    <!-- 7. Cookies -->
                    <article id="cookies" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-cookie text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                7. Cookies et technologies similaires
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>Notre site utilise des cookies pour améliorer votre expérience :</p>
                            
                            <div class="space-y-4 mt-6">
                                <div class="p-4 border border-outline/50 dark:border-outline-dark/50 rounded-radius">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Cookies essentiels</h5>
                                        <span class="px-2 py-1 text-xs rounded-radius bg-success/10 text-success">Obligatoires</span>
                                    </div>
                                    <p class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">Nécessaires au fonctionnement du site (session, sécurité, préférences de base).</p>
                                </div>
                                
                                <div class="p-4 border border-outline/50 dark:border-outline-dark/50 rounded-radius">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Cookies analytiques</h5>
                                        <span class="px-2 py-1 text-xs rounded-radius bg-primary/10 text-primary">Optionnels</span>
                                    </div>
                                    <p class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">Nous aident à comprendre comment les visiteurs utilisent notre site (Google Analytics).</p>
                                </div>
                                
                                <div class="p-4 border border-outline/50 dark:border-outline-dark/50 rounded-radius">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="font-semibold text-on-surface-strong dark:text-on-surface-dark-strong">Cookies de marketing</h5>
                                        <span class="px-2 py-1 text-xs rounded-radius bg-primary/10 text-primary">Optionnels</span>
                                    </div>
                                    <p class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">Utilisés pour personnaliser les communications et mesurer l'efficacité de nos campagnes.</p>
                                </div>
                            </div>

                            <p class="mt-6">Vous pouvez gérer vos préférences cookies à tout moment via le bandeau présent sur notre site ou dans les paramètres de votre navigateur.</p>
                        </div>
                    </article>

                    <!-- 8. Modifications -->
                    <article id="modifications" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-pencil-square text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                8. Modifications de la politique
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>Nous pouvons mettre à jour cette politique de confidentialité périodiquement pour refléter les évolutions de nos pratiques ou de la législation. Les modifications seront publiées sur cette page avec la date de mise à jour.</p>
                            
                            <div class="p-4 mt-6 bg-surface-alt dark:bg-surface-dark-alt rounded-radius border border-outline/30 dark:border-outline-dark/30">
                                <p class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">
                                    <i class="bi bi-info-circle-fill text-primary mr-2"></i>
                                    Nous vous encourageons à consulter régulièrement cette page pour rester informé de nos pratiques en matière de protection des données.
                                </p>
                            </div>
                        </div>
                    </article>

                    <!-- 9. Contact -->
                    <article id="contact" data-aos="fade-up" class="scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-radius bg-primary/10 flex items-center justify-center">
                                <i class="bi bi-envelope-fill text-primary"></i>
                            </div>
                            <h2 class="font-title text-2xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong">
                                9. Nous contacter
                            </h2>
                        </div>
                        <div class="prose prose-lg max-w-none text-on-surface/80 dark:text-on-surface-dark/80 font-paragraph space-y-4">
                            <p>Pour toute question concernant cette politique de confidentialité ou pour exercer vos droits, contactez notre Délégué à la Protection des Données (DPD) :</p>
                            
                            <div class="grid md:grid-cols-2 gap-6 mt-6">
                                <div class="p-6 bg-surface-alt dark:bg-surface-dark-alt rounded-radius border border-outline/50 dark:border-outline-dark/50">
                                    <h5 class="font-semibold text-on-surface-strong dark:text-on-surface-dark-strong mb-3 flex items-center gap-2">
                                        <i class="bi bi-envelope text-primary"></i>
                                        Email
                                    </h5>
                                    <a href="mailto:{{ $settings['site_email'] }}" class="text-primary hover:underline">{{ $settings['site_email'] }}</a>
                                </div>
                                
                                <div class="p-6 bg-surface-alt dark:bg-surface-dark-alt rounded-radius border border-outline/50 dark:border-outline-dark/50">
                                    <h5 class="font-semibold text-on-surface-strong dark:text-on-surface-dark-strong mb-3 flex items-center gap-2">
                                        <i class="bi bi-geo-alt text-primary"></i>
                                        Adresse postale
                                    </h5>
                                    <p class="text-sm">{{ $settings['site_adress'] }}</p>
                                </div>
                            </div>
                        </div>
                    </article>

                </div>

                <!-- CTA -->
                <div data-aos="fade-up" class="mt-16 p-8 bg-primary/5 dark:bg-primary/10 rounded-radius border border-primary/20 text-center">
                    <h3 class="font-title text-xl font-bold text-on-surface-strong dark:text-on-surface-dark-strong mb-3">
                        Des questions sur vos données ?
                    </h3>
                    <p class="text-on-surface/70 dark:text-on-surface-dark/70 font-paragraph mb-6">
                        Notre équipe est à votre disposition pour toute clarification.
                    </p>
                    <a hx-get="/contact" {!! $htmxAttrs !!} class="inline-flex cursor-pointer items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-radius font-semibold hover:opacity-90 transition">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span>Nous contacter</span>
                    </a>
                </div>

            </div>
        </section>

    </div>
@endsection