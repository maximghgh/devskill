// resources/js/components/editorjs-quiz.js

export default class QuizTool {
  static get toolbox() {
    return {
      title: 'Quiz',
      icon: `<svg width="20" height="20" viewBox="0 0 24 24">
               <path d="M3 4h18v2H3zm0 7h18v2H3zm0 7h18v2H3z"/>
             </svg>`
    };
  }

  constructor({ data }) {
    this.data = data && typeof data === 'object'
      ? data
      : { questions: [], settings: {} };
    this.data.questions = Array.isArray(this.data.questions) ? this.data.questions : [];

    this.wrapper = null;
  }

  render() {
    this.wrapper = document.createElement('div');
    this.wrapper.classList.add('quiz-wrapper');

    const questionsContainer = document.createElement('div');
    questionsContainer.classList.add('quiz-questions');
    this.wrapper.append(questionsContainer);

    // Рендерим сохранённые вопросы
    this.data.questions.forEach(q => this._renderQuestion(q, questionsContainer));

    // Кнопка "+ Добавить вопрос"
    const addQ = document.createElement('button');
    addQ.type = 'button';
    addQ.textContent = '+ Добавить вопрос';
    addQ.classList.add('quiz-add');
    addQ.addEventListener('click', e => {
      e.preventDefault();
      const newId = (this.data.questions[this.data.questions.length - 1]?.id || 0) + 1;
      const question = {
        id:      newId,
        prompt:  '',
        type:    'single',      // single | multiple | short | image_select
        options: ['', ''],      // для single/multiple
        answer:  0              // для single; для multiple: [], для short: 'строка', для image_select: [{image, options[], answer}]
      };
      this.data.questions.push(question);
      this._renderQuestion(question, questionsContainer);
    });
    this.wrapper.append(addQ);

    // немного стилей для превью
    if (!document.getElementById('quiztool-inline-style')) {
      const st = document.createElement('style');
      st.id = 'quiztool-inline-style';
      st.textContent = `
        .quiz-img-preview{width:100%;max-height:120px;object-fit:contain;border:1px solid #e5e7eb;border-radius:6px}
        .quiz-img-row{display:grid;grid-template-columns: 1fr 260px 48px;gap:8px;align-items:center;margin-bottom:8px}
        .quiz-img-controls{display:grid;grid-template-columns: 1fr 1fr; gap:8px}
        .quiz-option-row.selected{background:#f3f4f6}
      `;
      document.head.appendChild(st);
    }

    return this.wrapper;
  }

  _renderQuestion(question, container) {
    const qDiv = document.createElement('div');
    qDiv.classList.add('quiz-question');

    // Header
    const header = document.createElement('div');
    header.classList.add('quiz-question-header');
    header.innerHTML = `
      <h4>Вопрос #${question.id}</h4>
      <button type="button" class="quiz-question-delete">×</button>
    `;
    header.querySelector('.quiz-question-delete')
      .addEventListener('click', () => {
        this.data.questions = this.data.questions.filter(q => q !== question);
        qDiv.remove();
      });
    qDiv.append(header);

    // Текст вопроса
    const promptInput = document.createElement('input');
    promptInput.type = 'text';
    promptInput.placeholder = 'Текст вопроса';
    promptInput.value = question.prompt || '';
    promptInput.classList.add('quiz-prompt');
    promptInput.addEventListener('input', e => question.prompt = e.target.value);
    qDiv.append(promptInput);

    // Селектор типа
    const typeSelect = document.createElement('select');
    typeSelect.classList.add('quiz-type');
    [
      ['single','Один ответ'],
      ['multiple','Несколько ответов'],
      ['short','Краткий ответ'],
      ['image_select','Картинка + список']
    ].forEach(([v, t]) => {
      const o = document.createElement('option');
      o.value = v; o.textContent = t;
      if (question.type === v) o.selected = true;
      typeSelect.append(o);
    });
    qDiv.append(typeSelect);

    // Контейнер тела (в зависимости от типа)
    const body = document.createElement('div');
    body.classList.add('quiz-body');
    qDiv.append(body);

    const rerenderBody = () => {
      body.innerHTML = '';
      if (question.type === 'single' || question.type === 'multiple') {
        // Метка "Правильный ответ"
        const answerLabel = document.createElement('div');
        answerLabel.classList.add('quiz-answer-label');
        answerLabel.textContent = 'Правильный ответ:';
        body.append(answerLabel);

        // Контейнер опций
        const optsDiv = document.createElement('div');
        optsDiv.classList.add('quiz-options');
        body.append(optsDiv);

        // Если мы только что переключили тип — инициализируем answer правильно
        if (question.type === 'single' && !Number.isInteger(question.answer)) question.answer = 0;
        if (question.type === 'multiple' && !Array.isArray(question.answer))   question.answer = [];

        // Рендер + подсветка
        this._rerenderOptions(question, optsDiv);
        this._markSelectedRows(question, optsDiv);

        // Кнопка "+ Добавить опцию"
        const addOpt = document.createElement('button');
        addOpt.type = 'button';
        addOpt.textContent = '+ Добавить опцию';
        addOpt.classList.add('quiz-add-option');
        addOpt.addEventListener('click', () => {
          question.options.push('');
          this._rerenderOptions(question, optsDiv);
          this._markSelectedRows(question, optsDiv);
        });
        body.append(addOpt);
      }

      if (question.type === 'short') {
        const lbl = document.createElement('div');
        lbl.classList.add('quiz-answer-label');
        lbl.textContent = 'Правильный ответ (строка, регистр не важен):';
        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Например: стек';
        input.classList.add('quiz-prompt');
        input.value = (typeof question.answer === 'string') ? question.answer : '';
        input.addEventListener('input', e => question.answer = e.target.value);
        body.append(lbl, input);
      }

      if (question.type === 'image_select') {
        // Глобальные варианты (одни и те же для всех картинок)
        const globalLbl = document.createElement('div');
        globalLbl.classList.add('quiz-answer-label');
        globalLbl.textContent = 'Варианты ответов (через |), будут в каждом выпадающем списке:';
        const globalInp = document.createElement('input');
        globalInp.type = 'text';
        globalInp.placeholder = 'Ветвление | Цикл | Последовательность';
        globalInp.classList.add('quiz-prompt');
        const getGlobal = () =>
          (globalInp.value || '')
            .split('|')
            .map(s => s.trim())
            .filter(Boolean);

        // восстановление значения
        if (Array.isArray(question._globalOptionsCache)) {
          globalInp.value = question._globalOptionsCache.join(' | ');
        } else if (question.items?.length && Array.isArray(question.items[0].options)) {
          globalInp.value = question.items[0].options.join(' | ');
        } else {
          globalInp.value = '';
        }

        const rows = document.createElement('div');
        // элементы: [{ image, options:[...], answer:Number }]
        if (!Array.isArray(question.items)) question.items = [];

        const addRow = () => {
          const item = { image: '', options: getGlobal(), answer: 0 };
          question.items.push(item);
          rows.appendChild(this._renderImageRow(question, item, () => this._rebuildRowSelects(question, rows, getGlobal())));
        };

        // если нет строк — создаём одну
        if (!question.items.length) addRow();
        else {
          question.items.forEach(item => rows.appendChild(
            this._renderImageRow(question, item, () => this._rebuildRowSelects(question, rows, getGlobal()))
          ));
        }

        // при изменении глобальных вариантов — пересобираем селекты
        globalInp.addEventListener('input', () => {
          question._globalOptionsCache = getGlobal();
          this._rebuildRowSelects(question, rows, getGlobal());
        });

        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.textContent = '+ Добавить картинку';
        addBtn.classList.add('quiz-add-option');
        addBtn.addEventListener('click', addRow);

        body.append(globalLbl, globalInp, rows, addBtn);
      }
    };

    // первый рендер тела
    rerenderBody();

    // смена типа
    typeSelect.addEventListener('change', e => {
      const prev = question.type;
      question.type = e.target.value;

      // сбрасываем answer под новый тип
      if (question.type === 'single')     question.answer = 0;
      if (question.type === 'multiple')   question.answer = Array.isArray(question.answer) ? question.answer : [];
      if (question.type === 'short')      question.answer = '';
      if (question.type === 'image_select') question.items = [];

      rerenderBody();
    });

    container.append(qDiv);
  }

  _rerenderOptions(question, container) {
    container.innerHTML = '';
    (question.options || []).forEach((optText, idx) => {
      const row = document.createElement('div');
      row.classList.add('quiz-option-row');

      // 1) радио или чекбокс
      const inpSel = document.createElement('input');
      inpSel.type = question.type === 'single' ? 'radio' : 'checkbox';
      inpSel.name = `quiz-${question.id}`;
      inpSel.checked = question.type === 'single'
        ? question.answer === idx
        : (Array.isArray(question.answer) && question.answer.includes(idx));
      inpSel.classList.add('quiz-option-select');
      inpSel.addEventListener('change', () => {
        if (question.type === 'single') {
          question.answer = idx;
        } else {
          const s = new Set(Array.isArray(question.answer) ? question.answer : []);
          inpSel.checked ? s.add(idx) : s.delete(idx);
          question.answer = Array.from(s).sort((a,b)=>a-b);
        }
        this._markSelectedRows(question, container);
      });
      row.append(inpSel);

      // 2) текст варианта
      const inpText = document.createElement('input');
      inpText.type = 'text';
      inpText.placeholder = `Вариант ${idx + 1}`;
      inpText.value = optText || '';
      inpText.classList.add('quiz-option-text');
      inpText.addEventListener('input', e => question.options[idx] = e.target.value);
      row.append(inpText);

      // 3) удалить опцию
      const btnDel = document.createElement('button');
      btnDel.type = 'button';
      btnDel.textContent = '×';
      btnDel.classList.add('quiz-option-delete');
      btnDel.addEventListener('click', () => {
        // удаляем и корректируем ответы
        question.options.splice(idx, 1);
        if (question.type === 'single') {
          if (question.answer === idx) question.answer = 0;
          else if (question.answer > idx) question.answer -= 1;
        } else {
          question.answer = (question.answer || []).filter(i => i !== idx).map(i => i > idx ? i-1 : i);
        }
        this._rerenderOptions(question, container);
        this._markSelectedRows(question, container);
      });
      row.append(btnDel);

      container.append(row);
    });
  }

  _markSelectedRows(question, container) {
    Array.from(container.children).forEach((row, i) => {
      const selected = question.type === 'single'
        ? question.answer === i
        : (question.answer || []).includes(i);
      row.classList.toggle('selected', selected);
    });
  }

  /* ---------- image_select ---------- */

  _renderImageRow(question, item, onGlobalChanged) {
    const row = document.createElement('div');
    row.className = 'quiz-img-row';

    // Левая колонка: URL + превью
    const left = document.createElement('div');
    const url = document.createElement('input');
    url.type = 'text';
    url.placeholder = 'URL изображения';
    url.classList.add('quiz-prompt');
    url.value = item.image || '';
    url.addEventListener('input', e => {
      item.image = e.target.value;
      preview.src = item.image || '';
    });

    const preview = document.createElement('img');
    preview.className = 'quiz-img-preview';
    preview.alt = '';
    if (item.image) preview.src = item.image;

    left.append(url, preview);

    // Средняя колонка: выпадающий список вариантов
    const mid = document.createElement('div');
    mid.className = 'quiz-img-controls';

    // селект со списком глобальных вариантов
    const buildSelect = (opts, selectedIndex=0) => {
      const sel = document.createElement('select');
      (opts || []).forEach((t, i) => {
        const o = document.createElement('option');
        o.value = String(i); o.textContent = t;
        if (i === selectedIndex) o.selected = true;
        sel.append(o);
      });
      sel.addEventListener('change', e => {
        item.answer = parseInt(e.target.value, 10) || 0;
      });
      return sel;
    };

    const currentGlobal = Array.isArray(question._globalOptionsCache)
      ? question._globalOptionsCache
      : (Array.isArray(item.options) ? item.options : []);

    const sel = buildSelect(currentGlobal, Number.isInteger(item.answer) ? item.answer : 0);
    mid.append(sel);

    // Правая колонка: удалить строку
    const del = document.createElement('button');
    del.type = 'button';
    del.textContent = '×';
    del.classList.add('quiz-option-delete');
    del.addEventListener('click', () => {
      const idx = (question.items || []).indexOf(item);
      if (idx >= 0) question.items.splice(idx, 1);
      row.remove();
    });

    // при изменении глобальных вариантов — обновить селект
    row._rebuildSelect = (opts) => {
      const selected = Number.isInteger(item.answer) ? item.answer : 0;
      const newSel = buildSelect(opts, Math.min(selected, Math.max(opts.length-1,0)));
      mid.innerHTML = '';
      mid.append(newSel);
      item.options = opts.slice(); // сохраняем в item
    };

    row.append(left, mid, del);

    return row;
  }

  _rebuildRowSelects(question, rowsContainer, globalOptions) {
    // сохранить кэш глобальных опций
    question._globalOptionsCache = globalOptions.slice();
    // всем строкам обновить селекты
    rowsContainer.querySelectorAll('.quiz-img-row').forEach((rowEl, i) => {
      const rowApi = rowEl; // см. row._rebuildSelect
      if (typeof rowApi._rebuildSelect === 'function') {
        rowApi._rebuildSelect(globalOptions);
      }
      if (question.items[i]) question.items[i].options = globalOptions.slice();
    });
  }

  save() {
    // Ничего специально преобразовывать не нужно — мы всегда писали в this.data.questions
    // На выходе получаем единый формат:
    //  - single:    {type:'single', options:[...], answer:Number}
    //  - multiple:  {type:'multiple', options:[...], answer:[Number, ...]}
    //  - short:     {type:'short', answer:'строка'}
    //  - image_select: {type:'image_select', items:[{image:'', options:[...], answer:Number}, ...]}
    return this.data;
  }
}
