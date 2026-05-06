<?php if (isLoggedIn()): ?>
        </div><!-- .content-area -->
    </main>
</div><!-- .app-layout -->
<?php endif; ?>

<script src="<?= isset($isAdmin) ? '../script.js' : 'script.js' ?>"></script>
</body>
</html>
