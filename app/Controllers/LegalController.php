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
        <p>Les présentes conditions générales d\'utilisation (ci-après « CGU ») ont pour objet de définir les modalités et conditions dans lesquelles la plateforme MenuMiam (ci-après « le Service ») est mise à disposition des utilisateurs (ci-après « l\'Utilisateur »). Toute utilisation du Service implique l\'acceptation pleine et entière des présentes CGU.</p>
        
        <h3>2. Acceptation des CGU</h3>
        <p>L\'accès au Service et son utilisation sont subordonnés à l\'acceptation et au respect des présentes CGU. En accédant au Service, l\'Utilisateur reconnaît avoir pris connaissance de l\'ensemble des présentes CGU et les accepter sans réserve. MenuMiam se réserve le droit de modifier les CGU à tout moment. Les modifications prennent effet dès leur publication sur le site.</p>
        
        <h3>3. Description des services</h3>
        <p>MenuMiam est une plateforme en ligne permettant aux restaurateurs de :</p>
        <ul>
            <li>Créer et gérer leur carte de restaurant (mode éditable ou mode images)</li>
            <li>Personnaliser leur site vitrine (template, logo, bannière, informations de contact)</li>
            <li>Configurer les services proposés, moyens de paiement et réseaux sociaux</li>
            <li>Publier leur site vitrine accessible au public via un lien unique</li>
        </ul>
        
        <h3>4. Inscription et compte utilisateur</h3>
        <p>L\'accès au Service nécessite la création d\'un compte via une invitation envoyée par l\'administrateur de la plateforme. L\'Utilisateur s\'engage à fournir des informations exactes et à maintenir la confidentialité de ses identifiants de connexion. Toute utilisation du compte est réputée faite par l\'Utilisateur lui-même.</p>
        
        <h3>5. Responsabilités</h3>
        <p>MenuMiam met tout en œuvre pour assurer la disponibilité et la qualité du Service. Toutefois, MenuMiam ne saurait être tenu responsable :</p>
        <ul>
            <li>Des interruptions temporaires du Service pour maintenance ou mise à jour</li>
            <li>De la perte de données résultant d\'une utilisation non conforme</li>
            <li>Du contenu publié par les Utilisateurs sur leurs sites vitrines</li>
            <li>Des dommages indirects résultant de l\'utilisation du Service</li>
        </ul>
        <p>L\'Utilisateur est seul responsable du contenu qu\'il publie (textes, images, prix) et s\'engage à ne pas publier de contenu illicite, diffamatoire ou portant atteinte aux droits de tiers.</p>
        
        <h3>6. Propriété intellectuelle</h3>
        <p>L\'ensemble des éléments du Service (code source, design, logos, textes, images) sont la propriété exclusive de MenuMiam et sont protégés par les lois relatives à la propriété intellectuelle. Toute reproduction, représentation ou exploitation non autorisée est strictement interdite. Les contenus publiés par l\'Utilisateur restent sa propriété.</p>
        
        <h3>7. Résiliation</h3>
        <p>L\'Utilisateur peut supprimer son compte à tout moment depuis les paramètres de son interface d\'administration. MenuMiam se réserve le droit de suspendre ou résilier un compte en cas de non-respect des présentes CGU, sans préavis ni indemnité.</p>
        
        <h3>8. Droit applicable</h3>
        <p>Les présentes CGU sont régies par le droit français. Tout litige relatif à leur interprétation ou exécution relève de la compétence exclusive des tribunaux français.</p>
        
        <h3>9. Contact</h3>
        <p>Pour toute question concernant les CGU : <a href="mailto:contact@menumiam.dev">contact@menumiam.dev</a></p>
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
        <p><strong>MenuMiam</strong><br>
        Email : <a href="mailto:dpo@menumiam.dev">dpo@menumiam.dev</a></p>
        
        <h3>2. Données collectées</h3>
        <p>Dans le cadre de l\'utilisation du Service, nous collectons les données suivantes :</p>
        <ul>
            <li><strong>Données d\'identification :</strong> nom d\'utilisateur, adresse email, nom du restaurant</li>
            <li><strong>Données de connexion :</strong> mot de passe (hashé), adresse IP, date et heure de connexion</li>
            <li><strong>Données de contenu :</strong> carte du restaurant (catégories, plats, prix, descriptions, images), logo, bannière, informations de contact</li>
            <li><strong>Données techniques :</strong> type de navigateur, système d\'exploitation, résolution d\'écran</li>
            <li><strong>Cookies :</strong> voir notre <a href="?page=legal&section=cookies">Politique des Cookies</a></li>
        </ul>
        
        <h3>3. Base légale et finalités du traitement</h3>
        <p>Les données sont traitées sur les bases légales suivantes :</p>
        <ul>
            <li><strong>Exécution du contrat :</strong> gestion du compte, fourniture du service de création de carte en ligne</li>
            <li><strong>Intérêt légitime :</strong> amélioration du Service, sécurité, prévention des abus</li>
            <li><strong>Consentement :</strong> cookies analytiques et marketing (voir Politique des Cookies)</li>
            <li><strong>Obligation légale :</strong> conservation des données de connexion</li>
        </ul>
        
        <h3>4. Destinataires des données</h3>
        <p>Vos données personnelles ne sont pas vendues ni transmises à des tiers à des fins commerciales. Elles peuvent être communiquées aux sous-traitants techniques intervenant dans la fourniture du Service (hébergeur), dans le respect du RGPD.</p>
        
        <h3>5. Droits des utilisateurs</h3>
        <p>Conformément au Règlement (UE) 2016/679 (RGPD), vous disposez des droits suivants :</p>
        <ul>
            <li><strong>Droit d\'accès :</strong> obtenir la confirmation que des données vous concernant sont traitées et en obtenir une copie</li>
            <li><strong>Droit de rectification :</strong> demander la correction de données inexactes ou incomplètes</li>
            <li><strong>Droit à l\'effacement :</strong> demander la suppression de vos données (sous réserve des obligations légales)</li>
            <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré et lisible par machine</li>
            <li><strong>Droit d\'opposition :</strong> vous opposer au traitement de vos données pour des motifs légitimes</li>
            <li><strong>Droit à la limitation :</strong> demander la limitation du traitement dans certains cas</li>
        </ul>
        <p>Pour exercer ces droits, contactez-nous à : <a href="mailto:rgpd@menumiam.dev">rgpd@menumiam.dev</a>. Nous répondrons dans un délai de 30 jours.</p>
        <p>Vous pouvez également introduire une réclamation auprès de la CNIL (<a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>).</p>
        
        <h3>6. Conservation des données</h3>
        <p>Les données sont conservées pendant la durée d\'utilisation du Service. En cas de suppression du compte :</p>
        <ul>
            <li>Les données personnelles sont supprimées dans un délai de 30 jours</li>
            <li>Les données de connexion (logs) sont conservées 12 mois conformément à la loi</li>
            <li>Les sauvegardes sont purgées sous 90 jours</li>
        </ul>
        
        <h3>7. Sécurité des données</h3>
        <p>Nous mettons en œuvre les mesures techniques et organisationnelles appropriées pour protéger vos données : chiffrement des mots de passe (bcrypt), protection CSRF, requêtes préparées contre les injections SQL, headers de sécurité HTTP, session sécurisée.</p>
        
        <h3>8. Transferts internationaux</h3>
        <p>Vos données sont hébergées en France et ne font pas l\'objet de transferts en dehors de l\'Union Européenne.</p>
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
        <p>Un cookie est un petit fichier texte déposé sur votre terminal (ordinateur, smartphone, tablette) lors de la visite d\'un site web. Il permet au site de mémoriser des informations sur votre visite, comme vos préférences, afin de faciliter votre navigation ultérieure.</p>
        
        <h3>2. Cookies utilisés</h3>
        <p>Voici la liste exhaustive des cookies utilisés par MenuMiam :</p>
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
                <td>Maintien de la session utilisateur (connexion, panier, navigation)</td>
            </tr>
            <tr>
                <td>cookie_consent</td>
                <td>Essentiel</td>
                <td>1 an</td>
                <td>Mémoriser votre choix de consentement aux cookies (accepted, rejected, custom)</td>
            </tr>
            <tr>
                <td>cookie_analytics</td>
                <td>Analytique</td>
                <td>1 an</td>
                <td>Indique si vous avez accepté les cookies d\'analyse (true/false)</td>
            </tr>
            <tr>
                <td>cookie_marketing</td>
                <td>Marketing</td>
                <td>1 an</td>
                <td>Indique si vous avez accepté les cookies marketing (true/false)</td>
            </tr>
        </table>
        
        <h3>3. Catégories de cookies</h3>
        <ul>
            <li><strong>Cookies essentiels :</strong> indispensables au fonctionnement du site (session, consentement). Ils ne peuvent pas être désactivés.</li>
            <li><strong>Cookies analytiques :</strong> permettent de mesurer l\'audience et d\'améliorer le Service. Soumis à votre consentement.</li>
            <li><strong>Cookies marketing :</strong> utilisés pour personnaliser les publicités et suggestions. Soumis à votre consentement.</li>
        </ul>
        
        <h3>4. Gestion des cookies</h3>
        <p>Lors de votre première visite, une bannière de consentement vous permet de :</p>
        <ul>
            <li><strong>Accepter tous</strong> les cookies</li>
            <li><strong>Refuser tous</strong> les cookies non essentiels</li>
            <li><strong>Personnaliser</strong> vos préférences cookie par cookie</li>
        </ul>
        <p>Vous pouvez également gérer les cookies depuis les paramètres de votre navigateur. La suppression des cookies peut affecter votre expérience de navigation.</p>
        
        <h3>5. Cookies tiers</h3>
        <p>Nous n\'utilisons actuellement aucun cookie tiers (pas de Google Analytics, pas de pixels publicitaires). Si des outils tiers sont ajoutés à l\'avenir, cette politique sera mise à jour et votre consentement sera à nouveau sollicité.</p>
        
        <h3>6. Contact</h3>
        <p>Pour toute question relative aux cookies : <a href="mailto:contact@menumiam.dev">contact@menumiam.dev</a></p>
        ';
    }

    /**
     * @return string Contenu HTML des Mentions Légales
     */
    private function getLegalContent()
    {
        return '
        <h2>Mentions Légales</h2>
        <p><strong>Date de dernière mise à jour :</strong> ' . date('d/m/Y') . '</p>
        
        <h3>1. Éditeur du site</h3>
        <p><strong>MenuMiam</strong><br>
        Plateforme de création de cartes de restaurant en ligne<br>
        Email : <a href="mailto:contact@menumiam.dev">contact@menumiam.dev</a></p>
        <p><em>Note : les coordonnées complètes de l\'éditeur (adresse, téléphone, SIRET) seront ajoutées lors de la mise en production.</em></p>
        
        <h3>2. Directeur de publication</h3>
        <p><em>À compléter lors de la mise en production.</em></p>
        
        <h3>3. Hébergeur</h3>
        <p><em>Environnement actuel : serveur de développement local (WAMP). Les informations de l\'hébergeur de production seront ajoutées lors du déploiement.</em></p>
        
        <h3>4. Propriété intellectuelle</h3>
        <p>L\'ensemble du contenu du site MenuMiam (structure, design, code source, logos, textes, graphismes) est protégé par les dispositions du Code de la Propriété Intellectuelle. Toute reproduction, représentation, modification, publication, transmission ou exploitation de tout ou partie du site, par quelque procédé que ce soit, sans l\'autorisation écrite préalable de MenuMiam, est strictement interdite et constitue un délit de contrefaçon.</p>
        <p>Les contenus publiés par les utilisateurs (textes, images de plats, logos de restaurants) restent la propriété de leurs auteurs respectifs.</p>
        
        <h3>5. Limitations de responsabilité</h3>
        <p>MenuMiam s\'efforce de fournir un service fiable et disponible. Toutefois :</p>
        <ul>
            <li>Le Service est fourni « en l\'état » sans garantie d\'aucune sorte</li>
            <li>MenuMiam ne peut garantir l\'absence d\'interruptions, d\'erreurs ou de bugs</li>
            <li>MenuMiam décline toute responsabilité quant au contenu publié par les utilisateurs</li>
            <li>MenuMiam ne saurait être tenu responsable des dommages indirects résultant de l\'utilisation du Service</li>
        </ul>
        
        <h3>6. Protection des données</h3>
        <p>Pour toute information concernant la collecte et le traitement de vos données personnelles, veuillez consulter notre <a href="?page=legal&section=privacy">Politique de Confidentialité</a>.</p>
        
        <h3>7. Loi applicable et juridiction</h3>
        <p>Les présentes mentions légales sont régies par le droit français. Tout litige relatif à l\'utilisation du site sera soumis à la compétence exclusive des tribunaux français.</p>
        ';
    }
}
