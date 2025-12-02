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

<!-- Inclusion des scripts JavaScript -->
<?php if (!empty($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
        <script src="<?= htmlspecialchars($script) ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>