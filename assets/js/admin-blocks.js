/**
 * EDITOR DE BLOCOS - PAINEL ADMIN QADRO
 *
 * Monta o conteúdo como uma lista de blocos que podem ser adicionados,
 * editados, removidos e reordenados (arrastando pela alça ou pelas setas).
 * Os blocos são enviados no formulário como blocks[i][campo].
 *
 * Serve tanto ao corpo dos artigos do blog quanto ao conteúdo abaixo do
 * carrossel dos projetos: os endpoints das imagens vêm do próprio HTML
 * (data-upload-url / data-image-url), com os do blog como padrão.
 */
(function () {
  const editor = document.getElementById('blocksEditor');
  if (!editor) return;

  const list = document.getElementById('blocksList');
  const empty = document.getElementById('blocksEmpty');
  const types = JSON.parse(editor.dataset.types || '{}');
  const csrfToken = editor.dataset.csrf || '';
  const uploadUrl = editor.dataset.uploadUrl || 'blog_block_upload.php';
  const imageUrl = editor.dataset.imageUrl || 'blog_block_image.php';

  let blocks = [];
  try {
    const parsed = JSON.parse(editor.dataset.blocks || '[]');
    if (Array.isArray(parsed)) blocks = parsed;
  } catch (e) {
    blocks = [];
  }

  // ==========================================================================
  // Helpers
  // ==========================================================================

  /** Opções padrão (primeiro valor) de um tipo de bloco. */
  const defaultSettings = (type) => {
    const settings = {};
    const options = (types[type] && types[type].options) || {};
    Object.keys(options).forEach((option) => {
      settings[option] = Object.keys(options[option])[0];
    });
    return settings;
  };

  const escapeHtml = (value) =>
    String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');

  const blockImageUrl = (filename) =>
    imageUrl + '?file=' + encodeURIComponent(filename);

  // ==========================================================================
  // Renderização de um bloco
  // ==========================================================================

  const selectFor = (index, option, values, current) => {
    let html = `<label class="block-option">
      <span class="block-option-label">${escapeHtml(optionLabel(option))}</span>
      <select class="admin-select block-field" data-index="${index}" data-setting="${escapeHtml(option)}">`;
    Object.keys(values).forEach((value) => {
      const selected = String(current) === String(value) ? ' selected' : '';
      html += `<option value="${escapeHtml(value)}"${selected}>${escapeHtml(values[value])}</option>`;
    });
    html += '</select></label>';
    return html;
  };

  const optionLabel = (option) => {
    const labels = {
      width: 'Largura',
      align: 'Alinhamento',
      invert: 'Disposição',
      ratio: 'Proporção',
      size: 'Altura',
    };
    return labels[option] || option;
  };

  const imageField = (index, block) => {
    const has = block.image && block.image !== '';
    return `
      <div class="block-image-field">
        <div class="block-image-preview${has ? '' : ' is-empty'}">
          ${has
            ? `<img src="${escapeHtml(blockImageUrl(block.image))}" alt="">`
            : '<span>Nenhuma imagem escolhida</span>'}
        </div>
        <div class="block-image-actions">
          <label class="admin-btn admin-btn-ghost admin-btn-sm block-image-pick">
            ${has ? 'Trocar imagem' : 'Escolher imagem'}
            <input type="file" class="block-file" data-index="${index}" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden>
          </label>
          <span class="block-image-status" data-status="${index}"></span>
        </div>
        <input type="hidden" name="blocks[${index}][image]" value="${escapeHtml(block.image || '')}" data-image-input="${index}">
      </div>`;
  };

  const renderBlock = (block, index) => {
    const type = types[block.type];
    if (!type) return '';

    const settings = Object.assign(defaultSettings(block.type), block.settings || {});
    const item = document.createElement('div');
    item.className = 'block-item';
    item.draggable = false;
    item.dataset.index = index;

    let body = '';

    if (block.type === 'text' || block.type === 'quote') {
      const placeholder = block.type === 'quote'
        ? 'Frase curta de destaque'
        : 'Texto do parágrafo. Deixe uma linha em branco para separar parágrafos.';
      body += `<textarea class="admin-textarea ${block.type === 'quote' ? 'admin-textarea-sm' : ''} block-field"
                 name="blocks[${index}][content]" data-index="${index}" data-field="content"
                 placeholder="${escapeHtml(placeholder)}">${escapeHtml(block.content || '')}</textarea>`;
    }

    if (block.type === 'image') {
      body += imageField(index, block);
      body += `<label class="block-option block-option-wide">
                 <span class="block-option-label">Legenda (opcional)</span>
                 <input type="text" class="admin-input block-field" name="blocks[${index}][caption]"
                        data-index="${index}" data-field="caption" maxlength="255"
                        value="${escapeHtml(block.caption || '')}">
               </label>`;
    }

    if (block.type === 'split') {
      body += `<div class="block-split-grid">
                 <div>${imageField(index, block)}</div>
                 <textarea class="admin-textarea block-field" name="blocks[${index}][content]"
                           data-index="${index}" data-field="content"
                           placeholder="Texto que aparece ao lado da imagem.">${escapeHtml(block.content || '')}</textarea>
               </div>`;
    }

    if (block.type === 'spacer') {
      body += '<p class="block-spacer-hint">Bloco sem conteúdo: apenas o respiro vertical entre as seções.</p>';
    }

    let options = '';
    Object.keys(type.options || {}).forEach((option) => {
      options += selectFor(index, option, type.options[option], settings[option]);
    });
    if (options) {
      options = `<div class="block-options">${options}</div>`;
    }

    item.innerHTML = `
      <div class="block-head">
        <span class="block-drag" title="Arraste para reordenar">⠿</span>
        <span class="block-icon">${escapeHtml(type.icon)}</span>
        <span class="block-type-name">${escapeHtml(type.label)}</span>
        <div class="block-actions">
          <button type="button" class="block-btn" data-move="up" data-index="${index}" title="Mover para cima" aria-label="Mover para cima">↑</button>
          <button type="button" class="block-btn" data-move="down" data-index="${index}" title="Mover para baixo" aria-label="Mover para baixo">↓</button>
          <button type="button" class="block-btn block-btn-danger" data-remove="${index}" title="Excluir bloco" aria-label="Excluir bloco">✕</button>
        </div>
      </div>
      <div class="block-body">${body}${options}</div>
      <input type="hidden" name="blocks[${index}][type]" value="${escapeHtml(block.type)}">
      ${Object.keys(type.options || {})
        .map((option) => `<input type="hidden" name="blocks[${index}][settings][${escapeHtml(option)}]"
                            value="${escapeHtml(settings[option])}" data-settings-input="${index}-${escapeHtml(option)}">`)
        .join('')}
    `;

    return item;
  };

  const render = () => {
    list.innerHTML = '';
    blocks.forEach((block, index) => {
      const node = renderBlock(block, index);
      if (node) list.appendChild(node);
    });
    empty.style.display = blocks.length ? 'none' : '';
    bindDragHandles();
  };

  // ==========================================================================
  // Ações sobre os blocos
  // ==========================================================================

  const addBlock = (type) => {
    if (!types[type]) return;
    blocks.push({ type: type, content: '', image: '', caption: '', settings: defaultSettings(type) });
    render();
    const last = list.lastElementChild;
    if (last) {
      last.scrollIntoView({ behavior: 'smooth', block: 'center' });
      const firstInput = last.querySelector('textarea, input[type="text"]');
      if (firstInput) firstInput.focus();
    }
  };

  const moveBlock = (index, direction) => {
    const target = index + direction;
    if (target < 0 || target >= blocks.length) return;
    const [moved] = blocks.splice(index, 1);
    blocks.splice(target, 0, moved);
    render();
  };

  const removeBlock = (index) => {
    blocks.splice(index, 1);
    render();
  };

  // ==========================================================================
  // Eventos
  // ==========================================================================

  editor.querySelectorAll('[data-add-type]').forEach((button) => {
    button.addEventListener('click', () => addBlock(button.dataset.addType));
  });

  list.addEventListener('click', (event) => {
    const moveBtn = event.target.closest('[data-move]');
    if (moveBtn) {
      moveBlock(parseInt(moveBtn.dataset.index, 10), moveBtn.dataset.move === 'up' ? -1 : 1);
      return;
    }

    const removeBtn = event.target.closest('[data-remove]');
    if (removeBtn) {
      removeBlock(parseInt(removeBtn.dataset.remove, 10));
    }
  });

  // Mantém o array em sincronia com o que está digitado (o preview usa isso).
  list.addEventListener('input', (event) => {
    const field = event.target.closest('.block-field');
    if (!field) return;
    const index = parseInt(field.dataset.index, 10);
    if (isNaN(index) || !blocks[index]) return;

    if (field.dataset.setting) {
      blocks[index].settings = blocks[index].settings || {};
      blocks[index].settings[field.dataset.setting] = field.value;
      const hidden = list.querySelector(`[data-settings-input="${index}-${field.dataset.setting}"]`);
      if (hidden) hidden.value = field.value;
    } else if (field.dataset.field) {
      blocks[index][field.dataset.field] = field.value;
    }
  });

  list.addEventListener('change', (event) => {
    const field = event.target.closest('.block-field');
    if (field && field.dataset.setting) {
      list.dispatchEvent(new Event('input'));
    }

    const file = event.target.closest('.block-file');
    if (file && file.files && file.files[0]) {
      uploadBlockImage(file);
    }
  });

  /** Sobe a imagem na hora, para a prévia poder mostrá-la antes de salvar. */
  const uploadBlockImage = (input) => {
    const index = parseInt(input.dataset.index, 10);
    const status = list.querySelector(`[data-status="${index}"]`);
    if (status) status.textContent = 'Enviando...';

    const data = new FormData();
    data.append('csrf_token', csrfToken);
    data.append('image', input.files[0]);

    fetch(uploadUrl, { method: 'POST', body: data, credentials: 'same-origin' })
      .then((response) => response.json())
      .then((result) => {
        if (result.error) {
          if (status) status.textContent = result.error;
          return;
        }
        blocks[index].image = result.filename;
        render();
      })
      .catch(() => {
        if (status) status.textContent = 'Falha no envio. Tente novamente.';
      });
  };

  // --------------------------------------------------------------------------
  // Reordenar arrastando pela alça
  // --------------------------------------------------------------------------
  let dragIndex = null;

  const bindDragHandles = () => {
    list.querySelectorAll('.block-item').forEach((item) => {
      const handle = item.querySelector('.block-drag');
      if (!handle) return;

      handle.addEventListener('mousedown', () => { item.draggable = true; });
      item.addEventListener('mouseup', () => { item.draggable = false; });

      item.addEventListener('dragstart', (event) => {
        dragIndex = parseInt(item.dataset.index, 10);
        item.classList.add('is-dragging');
        event.dataTransfer.effectAllowed = 'move';
      });

      item.addEventListener('dragend', () => {
        item.classList.remove('is-dragging');
        item.draggable = false;
        list.querySelectorAll('.block-item').forEach((el) => el.classList.remove('is-over'));
      });

      item.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (dragIndex === null) return;
        item.classList.add('is-over');
      });

      item.addEventListener('dragleave', () => item.classList.remove('is-over'));

      item.addEventListener('drop', (event) => {
        event.preventDefault();
        item.classList.remove('is-over');
        const target = parseInt(item.dataset.index, 10);
        if (dragIndex === null || dragIndex === target) return;
        const [moved] = blocks.splice(dragIndex, 1);
        blocks.splice(target, 0, moved);
        dragIndex = null;
        render();
      });
    });
  };

  render();
})();
