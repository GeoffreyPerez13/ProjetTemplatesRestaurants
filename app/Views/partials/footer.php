    </div> <!-- .container : fermeture du conteneur principal -->

    <!-- Lightbox pour afficher les images en grand -->
    <div id="image-lightbox" class="lightbox">
        <span class="lightbox-close">&times;</span>
        <span class="lightbox-nav lightbox-prev">&#10094;</span>
        <span class="lightbox-nav lightbox-next">&#10095;</span>
        <div class="lightbox-content">
            <img id="lightbox-image" src="" alt="">
            <div id="lightbox-caption"></div>
        </div>
    </div>

    <!-- Footer avec mentions légales -->
    <footer class="admin-footer">
        <div class="footer-content">
            <div class="footer-left">
                <p>&copy; <?= date('Y') ?> MenuMiam - Interface d'administration</p>
            </div>
            <div class="footer-right">
                <nav class="footer-links">
                    <a href="?page=legal&section=cgu" target="_blank">CGU</a>
                    <span class="separator">|</span>
                    <a href="?page=legal&section=privacy" target="_blank">Confidentialité</a>
                    <span class="separator">|</span>
                    <a href="?page=legal&section=cookies" target="_blank">Cookies</a>
                    <span class="separator">|</span>
                    <a href="?page=legal&section=legal" target="_blank">Mentions légales</a>
                    <span class="separator">|</span>
                    <a href="mailto:contact@menumiam.dev">Contact</a>
                </nav>
            </div>
        </div>
    </footer>

</body>

</html>