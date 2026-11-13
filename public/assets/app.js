'use strict';

// JavaScript melhora a experiência. Cadastro, busca e exclusão também funcionam sem ele.
const passwordToggle = document.querySelector('#password-toggle');
passwordToggle?.addEventListener('click', () => {
  const input = document.querySelector('#senha');
  const visible = input.type === 'password';
  input.type = visible ? 'text' : 'password';
  passwordToggle.textContent = visible ? 'Ocultar' : 'Mostrar';
  passwordToggle.setAttribute('aria-pressed', String(visible));
});

const dialog = document.querySelector('#delete-dialog');
let deleteOpener;
document.addEventListener('click', (event) => {
  const link = event.target.closest('[data-delete-id]');
  if (!link || !dialog || typeof dialog.showModal !== 'function') return;
  event.preventDefault();
  deleteOpener = link;
  document.querySelector('#delete-book-id').value = link.dataset.deleteId;
  document.querySelector('#delete-book-name').textContent = link.dataset.deleteTitle;
  dialog.showModal();
  dialog.querySelector('[data-close-dialog]').focus();
});
dialog?.querySelector('[data-close-dialog]')?.addEventListener('click', () => dialog.close());
dialog?.addEventListener('close', () => deleteOpener?.focus());

const searchForm = document.querySelector('#search-form');
if (searchForm && 'fetch' in window && 'AbortController' in window) {
  const input = document.querySelector('#search');
  const results = document.querySelector('#catalog-results');
  const status = document.querySelector('#search-status');
  const clear = document.querySelector('#clear-search');
  let controller;
  let timer;
  let sequence = 0;

  async function search(page = 1, updateHistory = true) {
    clearTimeout(timer);
    controller?.abort();
    controller = new AbortController();
    const requestSequence = ++sequence;
    const params = new URLSearchParams({ q: input.value.trim(), pagina: page });
    results.setAttribute('aria-busy', 'true');
    status.classList.remove('error');
    status.textContent = 'Buscando no acervo...';
    try {
      const response = await fetch(`api/livros.php?${params}`, {
        signal: controller.signal,
        headers: { Accept: 'application/json' },
      });
      if (response.status === 401) {
        window.location.assign('login.php');
        return;
      }
      if (!response.ok) throw new Error('Falha na busca');
      const data = await response.json();
      if (requestSequence !== sequence) return;
      // O fragmento é renderizado pelo PHP, que escapa todos os dados do banco com htmlspecialchars.
      results.innerHTML = data.html;
      status.textContent = `${data.total} título(s) encontrado(s).`;
      clear.hidden = input.value.trim() === '';
      params.set('pagina', String(data.page));
      if (updateHistory) history.replaceState(null, '', `index.php?${params}`);
    } catch (error) {
      if (error.name === 'AbortError' || requestSequence !== sequence) return;
      status.classList.add('error');
      status.textContent = 'Não foi possível atualizar a busca. Confira sua conexão e tente novamente.';
    } finally {
      if (requestSequence === sequence) results.removeAttribute('aria-busy');
    }
  }

  input.addEventListener('input', () => {
    // Cancelamento imediato evita apresentar resultados de uma busca anterior.
    controller?.abort();
    sequence++;
    clearTimeout(timer);
    timer = setTimeout(() => search(), 300);
  });
  searchForm.addEventListener('submit', (event) => { event.preventDefault(); search(); });
  clear.addEventListener('click', (event) => { event.preventDefault(); input.value = ''; search(); input.focus(); });
  results.addEventListener('click', (event) => {
    const link = event.target.closest('[data-page-link]');
    if (!link) return;
    event.preventDefault();
    search(new URL(link.href).searchParams.get('pagina') || 1);
  });
}

// Validações no navegador ajudam o usuário; o PHP repete as regras antes de salvar.
const bookForm = document.querySelector('.book-form');
bookForm?.addEventListener('submit', (event) => {
  const price = bookForm.querySelector('#preco');
  price.setCustomValidity('');
  if (!/^\d{1,8}([.,]\d{1,2})?$/.test(price.value.trim())) {
    price.setCustomValidity('Use um valor de 0 a 99.999.999,99, com até duas casas decimais e sem separador de milhar.');
    price.reportValidity();
    event.preventDefault();
  }
});
bookForm?.querySelector('#preco')?.addEventListener('input', (event) => event.target.setCustomValidity(''));
