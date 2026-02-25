<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Contrôleur des pages légales (CGU, RGPD, Cookies, Mentions légales)
 * Affiche le contenu statique des différentes pages juridiques
 */
class LegalController extends BaseController
{
    /**
     * Affiche la page légale demandée selon le paramètre ?section=
     * Sections disponibles : cgu, privacy, cookies, legal
     */
    public function show()
    {
        $page = $_GET['section'] ?? 'cgu';

        $pages = [
            'cgu' => [
                'title' => 'Conditions Générales d\'Utilisation',
                'content' => $this->getCGUContent()
            ],
            'privacy' => [
                'title' => 'Politique de Confidentialité (RGPD)',
                'content' => $this->getPrivacyContent()
            ],
            'cookies' => [
                'title' => 'Politique des Cookies',
                'content' => $this->getCookiesContent()
            ],
            'legal' => [
                'title' => 'Mentions Légales',
                'content' => $this->getLegalContent()
            ]
        ];

        $currentPage = $pages[$page] ?? $pages['cgu'];

        $this->render('admin/legals', [
            'title' => $currentPage['title'],
            'content' => $currentPage['content'],
            'current_section' => $page,
            'sections' => array_keys($pages)
        ]);
    }

    /**
     * @return string Contenu HTML des Conditions Générales d'Utilisation
     */
    private function getCGUContent()
    {
        return '
        <h2>Conditions Générales d\'Utilisation</h2>
        <p><strong>Date de dernière mise à jour :</strong> ' . date('d/m/Y') . '</p>
        
        <h3>1. Objet</h3>
        <p>Les présentes conditions générales d\'utilisation (CGU) ont pour objet de définir les règles d\'utilisation du site MenuMiam...</p>
        
        <h3>2. Acceptation des CGU</h3>
        <p>L\'accès au site et son utilisation impliquent l\'acceptation sans réserve des présentes CGU...</p>
        
        <h3>3. Services proposés</h3>
        <p>MenuMiam propose aux restaurateurs de créer et gérer leur carte en ligne...</p>
        
        <h3>4. Responsabilités</h3>
        <p>MenuMiam met tout en œuvre pour assurer la qualité de ses services...</p>
        
        <h3>5. Propriété intellectuelle</h3>
        <p>Tous les éléments du site (textes, images, logos, etc.) sont protégés...</p>
        
        <h3>6. Contact</h3>
        <p>Pour toute question concernant les CGU : contact@menumiam.dev</p>
        ';
    }

    /**
     * @return string Contenu HTML de la Politique de Confidentialité (RGPD)
     */
    private function getPrivacyContent()
    {
        return '
        <h2>Politique de Confidentialité - RGPD</h2>
        <p><strong>Date de dernière mise à jour :</strong> ' . date('d/m/Y') . '</p>
        
        <h3>1. Responsable du traitement</h3>
        <p>MenuMiam<br>
        Email : dpo@menumiam.dev</p>
        
        <h3>2. Données collectées</h3>
        <p>Nous collectons :</p>
        <ul>
            <li>Données de contact (email, nom restaurant)</li>
            <li>Données de connexion</li>
            <li>Données de la carte du restaurant</li>
            <li>Données techniques (adresse IP, navigateur)</li>
        </ul>
        
        <h3>3. Finalités du traitement</h3>
        <p>Les données sont traitées pour :</p>
        <ul>
            <li>Gérer les comptes utilisateurs</li>
            <li>Fournir les services de gestion de carte</li>
            <li>Assurer la sécurité du site</li>
            <li>Respecter les obligations légales</li>
        </ul>
        
        <h3>4. Droits des utilisateurs</h3>
        <p>Conformément au RGPD, vous disposez des droits suivants :</p>
        <ul>
            <li>Droit d\'accès à vos données</li>
            <li>Droit de rectification</li>
            <li>Droit à l\'effacement</li>
            <li>Droit à la portabilité</li>
            <li>Droit d\'opposition</li>
        </ul>
        <p>Pour exercer ces droits : rgpd@menumiam.dev</p>
        
        <h3>5. Conservation des données</h3>
        <p>Les données sont conservées pendant la durée nécessaire...</p>
        ';
    }

    /**
     * @return string Contenu HTML de la Politique des Cookies
     */
    private function getCookiesContent()
    {
        return '
        <h2>Politique des Cookies</h2>
        <p><strong>Date de dernière mise à jour :</strong> ' . date('d/m/Y') . '</p>
        
        <h3>1. Qu\'est-ce qu\'un cookie ?</h3>
        <p>Un cookie est un petit fichier texte stocké sur votre appareil...</p>
        
        <h3>2. Cookies utilisés</h3>
        <table class="cookies-table">
            <tr>
                <th>Nom</th>
                <th>Type</th>
                <th>Durée</th>
                <th>Finalité</th>
            </tr>
            <tr>
                <td>PHPSESSID</td>
                <td>Essentiel</td>
                <td>Session</td>
                <td>Gestion de la connexion</td>
            </tr>
            <tr>
                <td>cookie_consent</td>
                <td>Essentiel</td>
                <td>1 an</td>
                <td>Mémoriser votre choix</td>
            </tr>
        </table>
        
        <h3>3. Gestion des cookies</h3>
        <p>Vous pouvez gérer les cookies via la bannière qui s\'affiche...</p>
        
        <h3>4. Cookies tiers</h3>
        <p>Nous n\'utilisons pas de cookies tiers pour le moment...</p>
        ';
    }

    /**
     * @return string Contenu HTML des Mentions Légales
     */
    private function getLegalContent()
    {
        return '
        <h2>Mentions Légales</h2>
        
        <h3>1. Éditeur du site</h3>
        <p><strong>MenuMiam</strong><br>
        [Votre adresse]<br>
        Email : contact@menumiam.dev<br>
        Téléphone : [Votre téléphone]</p>
        
        <h3>2. Directeur de publication</h3>
        <p>[Votre nom]</p>
        
        <h3>3. Hébergeur</h3>
        <p><strong>WAMP Local</strong><br>
        Serveur de développement local<br>
        Non accessible au public</p>
        
        <h3>4. Propriété intellectuelle</h3>
        <p>Le site et son contenu sont protégés par le droit d\'auteur...</p>
        
        <h3>5. Limitations de responsabilité</h3>
        <p>MenuMiam ne peut être tenu responsable des erreurs...</p>
        ';
    }
}
