<?php
$title = "Feuille de route - Configuration Services Premium";
$scripts = [];
$styles = ["css/admin/sections/settings/google-reviews-roadmap.css"];
?>

<div class="roadmap-container">
    <div class="roadmap-header">
        <h1><i class="fas fa-book"></i> Feuille de route - Configuration Services Premium</h1>
        <p class="roadmap-subtitle">Guide complet pour configurer les services premium pour les restaurants clients</p>
        <div class="roadmap-badge">
            <i class="fas fa-crown"></i> Super Admin uniquement
        </div>
    </div>

    <div class="roadmap-intro">
        <div class="intro-card">
            <h2><i class="fas fa-info-circle"></i> À propos de ce document</h2>
            <p>Ce document regroupe les procédures de configuration pour les services premium proposés aux restaurants clients. Chaque section détaille les étapes nécessaires pour mettre en place correctement chaque intégration.</p>
            <div class="intro-pricing">
                <strong>Services couverts :</strong> Avis Google, Intégration Livraison (Uber Eats, Deliveroo, Just Eat)
            </div>
        </div>
    </div>

    <!-- Navigation rapide -->
    <div class="roadmap-nav">
        <h3><i class="fas fa-list"></i> Navigation rapide</h3>
        <div class="nav-grid">
            <a href="#google-reviews" class="nav-card">
                <i class="fab fa-google"></i>
                <span>Avis Google</span>
            </a>
            <a href="#delivery-integration" class="nav-card">
                <i class="fas fa-motorcycle"></i>
                <span>Intégration Livraison</span>
            </a>
        </div>
    </div>

    <!-- ========================================
         SECTION 1 : AVIS GOOGLE
    ======================================== -->
    <div class="roadmap-section" id="google-reviews">
        <div class="section-header">
            <h2><i class="fab fa-google"></i> Configuration des Avis Google</h2>
            <p>Afficher les avis Google My Business sur le site vitrine du restaurant</p>
        </div>
    </div>

    <!-- Étape 1 : Prérequis -->
    <div class="roadmap-step">
        <div class="step-number">1</div>
        <div class="step-content">
            <h2><i class="fas fa-clipboard-check"></i> Prérequis</h2>
            <div class="step-description">
                <p>Avant de commencer, assurez-vous que le restaurant client dispose des éléments suivants :</p>
                <ul class="checklist">
                    <li><i class="fas fa-check-circle"></i> Une fiche Google My Business active et vérifiée</li>
                    <li><i class="fas fa-check-circle"></i> Un compte Google Cloud Platform (gratuit)</li>
                    <li><i class="fas fa-check-circle"></i> Une carte bancaire pour activer l'API (aucun frais si quota gratuit respecté)</li>
                    <li><i class="fas fa-check-circle"></i> L'option premium "Avis Google" activée sur MenuMiam</li>
                </ul>
            </div>
            <div class="step-note warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important :</strong> L'API Google Places est gratuite jusqu'à un certain quota mensuel. Au-delà, des frais peuvent s'appliquer. Informez le client de surveiller sa consommation.
            </div>
        </div>
    </div>

    <!-- Étape 2 : Créer un projet Google Cloud -->
    <div class="roadmap-step">
        <div class="step-number">2</div>
        <div class="step-content">
            <h2><i class="fas fa-cloud"></i> Créer un projet Google Cloud</h2>
            <div class="step-description">
                <ol class="step-list">
                    <li>
                        <strong>Accéder à Google Cloud Console</strong>
                        <p>Rendez-vous sur <a href="https://console.cloud.google.com" target="_blank">console.cloud.google.com</a></p>
                    </li>
                    <li>
                        <strong>Créer un nouveau projet</strong>
                        <ul>
                            <li>Cliquez sur le sélecteur de projet en haut de la page</li>
                            <li>Cliquez sur "Nouveau projet"</li>
                            <li>Nommez le projet (ex: "Restaurant-NomDuRestaurant-Reviews")</li>
                            <li>Cliquez sur "Créer"</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Sélectionner le projet</strong>
                        <p>Une fois créé, assurez-vous que le projet est bien sélectionné dans le sélecteur en haut de la page.</p>
                    </li>
                </ol>
            </div>
            <div class="step-screenshot">
                <div class="screenshot-placeholder">
                    <i class="fas fa-image"></i>
                    <p>Capture d'écran : Création d'un projet Google Cloud</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Étape 3 : Activer l'API Places -->
    <div class="roadmap-step">
        <div class="step-number">3</div>
        <div class="step-content">
            <h2><i class="fas fa-plug"></i> Activer l'API Places</h2>
            <div class="step-description">
                <ol class="step-list">
                    <li>
                        <strong>Accéder à la bibliothèque d'API</strong>
                        <p>Dans le menu latéral, allez dans "APIs & Services" → "Library"</p>
                        <p>Ou accédez directement via : <code>https://console.cloud.google.com/apis/library?project=VOTRE_PROJECT_ID</code></p>
                    </li>
                    <li>
                        <strong>Rechercher l'API Places</strong>
                        <p>Dans la barre de recherche, tapez "Places API"</p>
                    </li>
                    <li>
                        <strong>Activer l'API</strong>
                        <ul>
                            <li>Cliquez sur "Places API" dans les résultats</li>
                            <li>Cliquez sur le bouton "ENABLE" (Activer)</li>
                            <li>Attendez quelques secondes que l'activation soit effective</li>
                        </ul>
                    </li>
                </ol>
            </div>
            <div class="step-note info">
                <i class="fas fa-info-circle"></i>
                <strong>Note :</strong> L'activation de l'API peut prendre quelques minutes. Si vous obtenez une erreur lors des tests, attendez 5 minutes et réessayez.
            </div>
        </div>
    </div>

    <!-- Étape 4 : Créer une clé API -->
    <div class="roadmap-step">
        <div class="step-number">4</div>
        <div class="step-content">
            <h2><i class="fas fa-key"></i> Créer une clé API</h2>
            <div class="step-description">
                <ol class="step-list">
                    <li>
                        <strong>Accéder aux identifiants</strong>
                        <p>Dans le menu latéral, allez dans "APIs & Services" → "Credentials"</p>
                        <p>Ou accédez directement via : <code>https://console.cloud.google.com/apis/credentials?project=VOTRE_PROJECT_ID</code></p>
                    </li>
                    <li>
                        <strong>Créer une nouvelle clé</strong>
                        <ul>
                            <li>Cliquez sur "+ CREATE CREDENTIALS" en haut</li>
                            <li>Sélectionnez "API key"</li>
                            <li>Une clé sera générée automatiquement (format : <code>AIzaSy...</code>)</li>
                            <li><strong>Copiez immédiatement cette clé</strong> dans un endroit sûr</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Restreindre la clé (recommandé)</strong>
                        <ul>
                            <li>Cliquez sur "RESTRICT KEY" ou sur le nom de la clé</li>
                            <li>Dans "API restrictions", sélectionnez "Restrict key"</li>
                            <li>Cochez uniquement "Places API"</li>
                            <li>Dans "Application restrictions", vous pouvez :
                                <ul>
                                    <li>Laisser "None" pour les tests</li>
                                    <li>Ou restreindre par "HTTP referrers" et ajouter le domaine du restaurant</li>
                                </ul>
                            </li>
                            <li>Cliquez sur "Save"</li>
                        </ul>
                    </li>
                </ol>
            </div>
            <div class="step-note warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Sécurité :</strong> Ne partagez jamais cette clé API publiquement. Elle doit être gardée confidentielle et utilisée uniquement côté serveur.
            </div>
        </div>
    </div>

    <!-- Étape 5 : Trouver le Place ID -->
    <div class="roadmap-step">
        <div class="step-number">5</div>
        <div class="step-content">
            <h2><i class="fas fa-map-marker-alt"></i> Trouver le Google Place ID du restaurant</h2>
            <div class="step-description">
                <p>Le Place ID est l'identifiant unique de l'établissement sur Google Maps.</p>
                
                <h3>Méthode 1 : Place ID Finder (recommandée)</h3>
                <ol class="step-list">
                    <li>
                        <strong>Accéder au Place ID Finder</strong>
                        <p>Rendez-vous sur : <a href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder" target="_blank">Place ID Finder</a></p>
                    </li>
                    <li>
                        <strong>Rechercher le restaurant</strong>
                        <ul>
                            <li>Dans la barre de recherche, tapez le nom complet du restaurant</li>
                            <li>Ou tapez l'adresse exacte</li>
                            <li>Cliquez sur le marqueur qui apparaît sur la carte</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Copier le Place ID</strong>
                        <p>Le Place ID s'affiche dans l'infobulle (format : <code>ChIJ...</code>)</p>
                        <p><strong>Copiez ce Place ID</strong></p>
                    </li>
                </ol>

                <h3>Méthode 2 : Via Google Maps</h3>
                <ol class="step-list">
                    <li>Ouvrez Google Maps et recherchez le restaurant</li>
                    <li>Cliquez sur le restaurant pour ouvrir sa fiche</li>
                    <li>Regardez l'URL dans la barre d'adresse</li>
                    <li>Le Place ID se trouve après <code>!1s</code> dans l'URL</li>
                </ol>
            </div>
            <div class="step-note info">
                <i class="fas fa-lightbulb"></i>
                <strong>Astuce :</strong> Si le restaurant n'apparaît pas, vérifiez que sa fiche Google My Business est bien publiée et visible publiquement.
            </div>
        </div>
    </div>

    <!-- Étape 6 : Configuration dans MenuMiam -->
    <div class="roadmap-step">
        <div class="step-number">6</div>
        <div class="step-content">
            <h2><i class="fas fa-cog"></i> Configuration dans MenuMiam</h2>
            <div class="step-description">
                <ol class="step-list">
                    <li>
                        <strong>Se connecter en tant que restaurant</strong>
                        <p>Connectez-vous au compte du restaurant client sur MenuMiam</p>
                    </li>
                    <li>
                        <strong>Accéder aux paramètres Google Reviews</strong>
                        <ul>
                            <li>Allez dans "Paramètres" → "Avis Google"</li>
                            <li>Ou accédez directement via : <code>?page=settings&section=google-reviews</code></li>
                        </ul>
                    </li>
                    <li>
                        <strong>Remplir les champs de configuration</strong>
                        <ul>
                            <li><strong>Google Place ID :</strong> Collez le Place ID obtenu à l'étape 5</li>
                            <li><strong>Clé API Google :</strong> Collez la clé API créée à l'étape 4</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Enregistrer la configuration</strong>
                        <p>Cliquez sur "Enregistrer la configuration"</p>
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Étape 7 : Tests et validation -->
    <div class="roadmap-step">
        <div class="step-number">7</div>
        <div class="step-content">
            <h2><i class="fas fa-vial"></i> Tests et validation</h2>
            <div class="step-description">
                <ol class="step-list">
                    <li>
                        <strong>Générer des avis de test</strong>
                        <ul>
                            <li>Dans la section "Avis Google", cliquez sur "Générer des avis de test"</li>
                            <li>Vérifiez que les avis fictifs s'affichent correctement</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Récupérer les vrais avis</strong>
                        <ul>
                            <li>Cliquez sur "Récupérer les avis Google"</li>
                            <li>Vérifiez que les vrais avis du restaurant s'affichent</li>
                            <li>Vérifiez la note moyenne et le nombre d'avis</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Vérifier l'affichage sur la vitrine</strong>
                        <ul>
                            <li>Accédez à la page vitrine du restaurant</li>
                            <li>Vérifiez que les avis s'affichent correctement</li>
                            <li>Testez sur mobile et desktop</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Tester le rafraîchissement automatique</strong>
                        <p>Les avis sont automatiquement mis à jour toutes les 24h. Informez le client de ce délai.</p>
                    </li>
                </ol>
            </div>
            <div class="step-note success">
                <i class="fas fa-check-circle"></i>
                <strong>Succès :</strong> Si tous les tests passent, la configuration est terminée ! Les avis Google sont maintenant actifs sur le site du restaurant.
            </div>
        </div>
    </div>

    <!-- Étape 8 : Dépannage -->
    <div class="roadmap-step">
        <div class="step-number">8</div>
        <div class="step-content">
            <h2><i class="fas fa-wrench"></i> Dépannage et problèmes courants</h2>
            <div class="step-description">
                <div class="troubleshooting-grid">
                    <div class="trouble-item">
                        <h3><i class="fas fa-times-circle"></i> Erreur : "API key not valid"</h3>
                        <p><strong>Causes possibles :</strong></p>
                        <ul>
                            <li>La clé API est incorrecte ou mal copiée</li>
                            <li>L'API Places n'est pas activée</li>
                            <li>Les restrictions de la clé bloquent l'accès</li>
                        </ul>
                        <p><strong>Solutions :</strong></p>
                        <ul>
                            <li>Vérifiez que la clé est correctement copiée (sans espaces)</li>
                            <li>Vérifiez que l'API Places est bien activée</li>
                            <li>Retirez temporairement les restrictions pour tester</li>
                        </ul>
                    </div>

                    <div class="trouble-item">
                        <h3><i class="fas fa-times-circle"></i> Erreur : "Place not found"</h3>
                        <p><strong>Causes possibles :</strong></p>
                        <ul>
                            <li>Le Place ID est incorrect</li>
                            <li>La fiche Google My Business n'est pas publiée</li>
                        </ul>
                        <p><strong>Solutions :</strong></p>
                        <ul>
                            <li>Vérifiez le Place ID avec le Place ID Finder</li>
                            <li>Vérifiez que la fiche est visible sur Google Maps</li>
                        </ul>
                    </div>

                    <div class="trouble-item">
                        <h3><i class="fas fa-times-circle"></i> Aucun avis ne s'affiche</h3>
                        <p><strong>Causes possibles :</strong></p>
                        <ul>
                            <li>Le restaurant n'a pas encore d'avis sur Google</li>
                            <li>Les avis ne sont pas publics</li>
                        </ul>
                        <p><strong>Solutions :</strong></p>
                        <ul>
                            <li>Vérifiez sur Google Maps que le restaurant a des avis</li>
                            <li>Générez des avis de test pour vérifier que le système fonctionne</li>
                        </ul>
                    </div>

                    <div class="trouble-item">
                        <h3><i class="fas fa-times-circle"></i> Erreur : "Quota exceeded"</h3>
                        <p><strong>Causes possibles :</strong></p>
                        <ul>
                            <li>Le quota gratuit de l'API est dépassé</li>
                        </ul>
                        <p><strong>Solutions :</strong></p>
                        <ul>
                            <li>Vérifiez la consommation dans Google Cloud Console</li>
                            <li>Activez la facturation si nécessaire</li>
                            <li>Réduisez la fréquence de rafraîchissement des avis</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Étape 9 : Facturation et suivi -->
    <div class="roadmap-step">
        <div class="step-number">9</div>
        <div class="step-content">
            <h2><i class="fas fa-euro-sign"></i> Facturation et suivi</h2>
            <div class="step-description">
                <h3>Coûts Google Cloud</h3>
                <div class="pricing-info">
                    <p><strong>API Places (New) :</strong></p>
                    <ul>
                        <li>Quota gratuit : 200 000 requêtes/mois</li>
                        <li>Au-delà : 0,017€ par requête</li>
                    </ul>
                    <p><strong>Estimation pour un restaurant :</strong></p>
                    <ul>
                        <li>1 rafraîchissement/jour = ~30 requêtes/mois</li>
                        <li>Coût mensuel estimé : <strong>0€</strong> (dans le quota gratuit)</li>
                    </ul>
                </div>

                <h3>Facturation MenuMiam</h3>
                <p>Ce service peut être facturé en supplément :</p>
                <ul>
                    <li><strong>Configuration initiale :</strong> Frais uniques (à définir)</li>
                    <li><strong>Maintenance mensuelle :</strong> Inclus dans l'option premium "Avis Google" (5€/mois)</li>
                </ul>

                <h3>Suivi et maintenance</h3>
                <ul>
                    <li>Vérifiez régulièrement que les avis se mettent à jour</li>
                    <li>Surveillez les quotas Google Cloud du client</li>
                    <li>Informez le client en cas de dépassement de quota</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- ========================================
         SECTION 2 : INTÉGRATION LIVRAISON
    ======================================== -->
    <div class="roadmap-section" id="delivery-integration">
        <div class="section-header">
            <h2><i class="fas fa-motorcycle"></i> Configuration de l'Intégration Livraison</h2>
            <p>Connecter les plateformes de livraison (Uber Eats, Deliveroo, Just Eat) pour centraliser les commandes</p>
        </div>
    </div>

    <!-- Étape 1 : Prérequis Livraison -->
    <div class="roadmap-step">
        <div class="step-number">1</div>
        <div class="step-content">
            <h2><i class="fas fa-clipboard-check"></i> Prérequis</h2>
            <div class="step-description">
                <p>Avant de configurer l'intégration livraison, vérifiez que :</p>
                <ul class="checklist">
                    <li><i class="fas fa-check-circle"></i> Le restaurant a des comptes actifs sur les plateformes (Uber Eats, Deliveroo, Just Eat)</li>
                    <li><i class="fas fa-check-circle"></i> Le restaurant a accès aux espaces partenaires de chaque plateforme</li>
                    <li><i class="fas fa-check-circle"></i> L'option premium "Intégration livraison" est activée sur MenuMiam (7€/mois)</li>
                </ul>
            </div>
            <div class="step-note info">
                <i class="fas fa-info-circle"></i>
                <strong>Note :</strong> Chaque plateforme nécessite une configuration séparée. Le restaurant peut choisir de connecter une, deux ou les trois plateformes.
            </div>
        </div>
    </div>

    <!-- Étape 2 : Uber Eats -->
    <div class="roadmap-step">
        <div class="step-number">2</div>
        <div class="step-content">
            <h2><i class="fas fa-utensils"></i> Configuration Uber Eats</h2>
            <div class="step-description">
                <h3>Obtenir les identifiants API</h3>
                <ol class="step-list">
                    <li>
                        <strong>Accéder à l'espace partenaire</strong>
                        <p>Connectez-vous sur <a href="https://restaurant.uber.com" target="_blank">restaurant.uber.com</a></p>
                    </li>
                    <li>
                        <strong>Accéder aux paramètres API</strong>
                        <ul>
                            <li>Menu → Paramètres → Intégrations</li>
                            <li>Section "API & Webhooks"</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Générer une clé API</strong>
                        <ul>
                            <li>Cliquez sur "Générer une nouvelle clé"</li>
                            <li>Nommez-la "MenuMiam Integration"</li>
                            <li><strong>Copiez immédiatement la clé</strong> (format : <code>ue_live_...</code>)</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Récupérer l'ID Restaurant</strong>
                        <p>Dans Paramètres → Informations du restaurant → ID unique (format : <code>xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</code>)</p>
                    </li>
                </ol>

                <h3>Configuration dans MenuMiam</h3>
                <ol class="step-list">
                    <li>Allez dans Paramètres → Intégration livraison</li>
                    <li>Activez le toggle "Uber Eats"</li>
                    <li>Remplissez les champs :
                        <ul>
                            <li><strong>Clé API :</strong> Collez la clé générée</li>
                            <li><strong>ID Restaurant :</strong> Collez l'ID unique</li>
                        </ul>
                    </li>
                    <li>Cliquez sur "Tester" pour vérifier la connexion</li>
                    <li>Si le test réussit, cliquez sur "Enregistrer"</li>
                </ol>

                <h3>Configurer le Webhook</h3>
                <ol class="step-list">
                    <li>Dans MenuMiam, copiez l'URL Webhook générée automatiquement</li>
                    <li>Retournez dans l'espace partenaire Uber Eats</li>
                    <li>Paramètres → Intégrations → Webhooks</li>
                    <li>Ajoutez l'URL copiée</li>
                    <li>Sélectionnez les événements : "Nouvelle commande", "Commande annulée", "Statut modifié"</li>
                    <li>Enregistrez</li>
                </ol>
            </div>
            <div class="step-note warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Sécurité :</strong> Ne partagez jamais les clés API. Elles donnent un accès complet au compte du restaurant.
            </div>
        </div>
    </div>

    <!-- Étape 3 : Deliveroo -->
    <div class="roadmap-step">
        <div class="step-number">3</div>
        <div class="step-content">
            <h2><i class="fas fa-motorcycle"></i> Configuration Deliveroo</h2>
            <div class="step-description">
                <h3>Obtenir les identifiants API</h3>
                <ol class="step-list">
                    <li>
                        <strong>Accéder à l'espace partenaire</strong>
                        <p>Connectez-vous sur <a href="https://restaurant-hub.deliveroo.com" target="_blank">restaurant-hub.deliveroo.com</a></p>
                    </li>
                    <li>
                        <strong>Demander l'accès API</strong>
                        <p>Deliveroo nécessite une demande d'accès API :</p>
                        <ul>
                            <li>Contactez le support Deliveroo via l'espace partenaire</li>
                            <li>Demandez l'activation de l'API Partner</li>
                            <li>Précisez que c'est pour une intégration avec MenuMiam</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Récupérer les identifiants</strong>
                        <p>Une fois approuvé, vous recevrez :</p>
                        <ul>
                            <li><strong>Clé API :</strong> Format <code>roo_live_...</code></li>
                            <li><strong>ID Restaurant :</strong> Identifiant unique du restaurant</li>
                        </ul>
                    </li>
                </ol>

                <h3>Configuration dans MenuMiam</h3>
                <p>Même processus qu'Uber Eats :</p>
                <ol class="step-list">
                    <li>Activez le toggle "Deliveroo"</li>
                    <li>Remplissez Clé API et ID Restaurant</li>
                    <li>Testez la connexion</li>
                    <li>Configurez le Webhook dans l'espace Deliveroo</li>
                    <li>Enregistrez</li>
                </ol>
            </div>
            <div class="step-note info">
                <i class="fas fa-lightbulb"></i>
                <strong>Délai :</strong> L'activation de l'API Deliveroo peut prendre 2-3 jours ouvrés. Anticipez cette demande.
            </div>
        </div>
    </div>

    <!-- Étape 4 : Just Eat -->
    <div class="roadmap-step">
        <div class="step-number">4</div>
        <div class="step-content">
            <h2><i class="fas fa-pizza-slice"></i> Configuration Just Eat</h2>
            <div class="step-description">
                <h3>Obtenir les identifiants API</h3>
                <ol class="step-list">
                    <li>
                        <strong>Accéder à l'espace partenaire</strong>
                        <p>Connectez-vous sur <a href="https://partner.just-eat.fr" target="_blank">partner.just-eat.fr</a></p>
                    </li>
                    <li>
                        <strong>Accéder aux paramètres API</strong>
                        <ul>
                            <li>Menu → Paramètres → Intégrations & API</li>
                            <li>Section "API Partner"</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Générer une clé API</strong>
                        <ul>
                            <li>Cliquez sur "Créer une clé API"</li>
                            <li>Sélectionnez les permissions : "Lecture commandes", "Mise à jour statuts"</li>
                            <li><strong>Copiez la clé</strong> (format : <code>je_live_...</code>)</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Récupérer l'ID Restaurant</strong>
                        <p>Visible dans Paramètres → Informations du restaurant</p>
                    </li>
                </ol>

                <h3>Configuration dans MenuMiam</h3>
                <p>Même processus que les autres plateformes :</p>
                <ol class="step-list">
                    <li>Activez le toggle "Just Eat"</li>
                    <li>Remplissez les identifiants</li>
                    <li>Testez et enregistrez</li>
                    <li>Configurez le Webhook</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Étape 5 : Tests et validation -->
    <div class="roadmap-step">
        <div class="step-number">5</div>
        <div class="step-content">
            <h2><i class="fas fa-vial"></i> Tests et validation</h2>
            <div class="step-description">
                <ol class="step-list">
                    <li>
                        <strong>Vérifier les statistiques</strong>
                        <p>Dans la section Intégration livraison, vérifiez que :</p>
                        <ul>
                            <li>Le nombre de plateformes connectées est correct</li>
                            <li>La dernière synchronisation est récente</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Tester avec une commande réelle</strong>
                        <ul>
                            <li>Passez une commande de test sur chaque plateforme</li>
                            <li>Vérifiez que la commande apparaît dans MenuMiam</li>
                            <li>Testez la mise à jour du statut</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Former le restaurant</strong>
                        <p>Expliquez au client comment :</p>
                        <ul>
                            <li>Consulter les commandes centralisées</li>
                            <li>Mettre à jour les statuts</li>
                            <li>Gérer les problèmes de synchronisation</li>
                        </ul>
                    </li>
                </ol>
            </div>
            <div class="step-note success">
                <i class="fas fa-check-circle"></i>
                <strong>Succès :</strong> Si les commandes de test apparaissent correctement, l'intégration est opérationnelle !
            </div>
        </div>
    </div>

    <!-- Étape 6 : Dépannage Livraison -->
    <div class="roadmap-step">
        <div class="step-number">6</div>
        <div class="step-content">
            <h2><i class="fas fa-wrench"></i> Dépannage</h2>
            <div class="step-description">
                <div class="troubleshooting-grid">
                    <div class="trouble-item">
                        <h3><i class="fas fa-times-circle"></i> Erreur : "Connexion échouée"</h3>
                        <p><strong>Causes possibles :</strong></p>
                        <ul>
                            <li>Clé API incorrecte ou expirée</li>
                            <li>ID Restaurant incorrect</li>
                            <li>Permissions API insuffisantes</li>
                        </ul>
                        <p><strong>Solutions :</strong></p>
                        <ul>
                            <li>Vérifiez que la clé est correctement copiée (sans espaces)</li>
                            <li>Régénérez une nouvelle clé API</li>
                            <li>Vérifiez les permissions dans l'espace partenaire</li>
                        </ul>
                    </div>

                    <div class="trouble-item">
                        <h3><i class="fas fa-times-circle"></i> Les commandes n'apparaissent pas</h3>
                        <p><strong>Causes possibles :</strong></p>
                        <ul>
                            <li>Webhook mal configuré</li>
                            <li>URL Webhook incorrecte</li>
                            <li>Événements non sélectionnés</li>
                        </ul>
                        <p><strong>Solutions :</strong></p>
                        <ul>
                            <li>Vérifiez l'URL Webhook dans l'espace partenaire</li>
                            <li>Vérifiez que tous les événements sont activés</li>
                            <li>Testez le Webhook avec l'outil de test de la plateforme</li>
                        </ul>
                    </div>

                    <div class="trouble-item">
                        <h3><i class="fas fa-times-circle"></i> Synchronisation lente</h3>
                        <p><strong>Causes possibles :</strong></p>
                        <ul>
                            <li>Problème réseau</li>
                            <li>Plateforme en maintenance</li>
                        </ul>
                        <p><strong>Solutions :</strong></p>
                        <ul>
                            <li>Vérifiez la connexion internet du restaurant</li>
                            <li>Consultez le statut des plateformes</li>
                            <li>Contactez le support de la plateforme</li>
                        </ul>
                    </div>

                    <div class="trouble-item">
                        <h3><i class="fas fa-times-circle"></i> Erreur : "API key expired"</h3>
                        <p><strong>Causes possibles :</strong></p>
                        <ul>
                            <li>La clé API a expiré (certaines plateformes ont des clés temporaires)</li>
                        </ul>
                        <p><strong>Solutions :</strong></p>
                        <ul>
                            <li>Générez une nouvelle clé API</li>
                            <li>Mettez à jour la configuration dans MenuMiam</li>
                            <li>Testez à nouveau la connexion</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Étape 7 : Facturation Livraison -->
    <div class="roadmap-step">
        <div class="step-number">7</div>
        <div class="step-content">
            <h2><i class="fas fa-euro-sign"></i> Facturation</h2>
            <div class="step-description">
                <h3>Coûts des plateformes</h3>
                <div class="pricing-info">
                    <p><strong>Uber Eats, Deliveroo, Just Eat :</strong></p>
                    <ul>
                        <li>API gratuite pour les restaurants partenaires</li>
                        <li>Pas de frais supplémentaires pour l'intégration</li>
                        <li>Les commissions habituelles s'appliquent (15-35% selon plateforme)</li>
                    </ul>
                </div>

                <h3>Facturation MenuMiam</h3>
                <ul>
                    <li><strong>Configuration initiale :</strong> Frais uniques (à définir selon le nombre de plateformes)</li>
                    <li><strong>Abonnement mensuel :</strong> Inclus dans l'option premium "Intégration livraison" (7€/mois)</li>
                    <li><strong>Support :</strong> Assistance technique incluse</li>
                </ul>

                <h3>Suivi</h3>
                <ul>
                    <li>Vérifiez régulièrement que les commandes se synchronisent</li>
                    <li>Surveillez les statistiques de commandes</li>
                    <li>Assurez un support réactif au client</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Footer avec actions -->
    <div class="roadmap-footer">
        <div class="footer-actions">
            <a href="?page=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au dashboard
            </a>
            <button onclick="window.print()" class="btn btn-outline">
                <i class="fas fa-print"></i> Imprimer cette feuille de route
            </button>
        </div>
        <div class="footer-info">
            <p><i class="fas fa-info-circle"></i> Cette feuille de route est réservée aux super administrateurs MenuMiam.</p>
            <p>Pour toute question ou assistance, contactez le support technique.</p>
        </div>
    </div>
</div>
