        </main>
        <footer class="footer"><span>© <?= date('Y') ?> Editora Fonteles</span><span>Organização que abre novos capítulos.</span></footer>
    </div>
</div>
<dialog id="delete-dialog" class="confirm-dialog" aria-labelledby="delete-title">
    <div class="dialog-symbol"><?= icon('trash') ?></div>
    <p class="eyebrow">GESTÃO DO ACERVO</p><h2 id="delete-title">Excluir este livro?</h2>
    <p>O título <strong id="delete-book-name"></strong> será removido do acervo. Esta ação não pode ser desfeita.</p>
    <form action="excluir.php" method="post"><?= csrf_field() ?><input type="hidden" name="id" id="delete-book-id"><div class="dialog-actions"><button type="button" class="button secondary" data-close-dialog>Cancelar</button><button class="button danger">Excluir livro</button></div></form>
</dialog>
</body></html>
