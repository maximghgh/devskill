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
    this.data = data || { questions: [], settings: {} };
  }

  render() {
    const wrapper = document.createElement('div');
    wrapper.classList.add('quiz-wrapper');

    const questionsContainer = document.createElement('div');
    questionsContainer.classList.add('quiz-questions');
    wrapper.append(questionsContainer);

    // Рендерим сохранённые вопросы
    this.data.questions.forEach(q =>
      this._renderQuestion(q, questionsContainer)
    );

    // Кнопка "+ Добавить вопрос"
    const addQ = document.createElement('button');
    addQ.type = 'button';
    addQ.textContent = '+ Добавить вопрос';
    addQ.classList.add('quiz-add');
    addQ.addEventListener('click', e => {
      e.preventDefault();
      const newId = this.data.questions.length + 1;
      const question = {
        id:      newId,
        prompt:  '',
        type:    'single',
        options: ['', ''],
        answer:  0
      };
      this.data.questions.push(question);
      this._renderQuestion(question, questionsContainer);
    });
    wrapper.append(addQ);

    return wrapper;
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
    promptInput.value = question.prompt;
    promptInput.classList.add('quiz-prompt');
    promptInput.addEventListener('input', e =>
      question.prompt = e.target.value
    );
    qDiv.append(promptInput);

    // Селектор типа
    const typeSelect = document.createElement('select');
    typeSelect.classList.add('quiz-type');
    [
      ['single','Один ответ'],
      ['multiple','Несколько ответов']
    ].forEach(([v, t]) => {
      const o = document.createElement('option');
      o.value = v; o.textContent = t;
      if (question.type === v) o.selected = true;
      typeSelect.append(o);
    });
    qDiv.append(typeSelect);

    // Метка "Правильный ответ"
    const answerLabel = document.createElement('div');
    answerLabel.classList.add('quiz-answer-label');
    answerLabel.textContent = 'Правильный ответ:';
    qDiv.append(answerLabel);

    // Контейнер опций
    const optsDiv = document.createElement('div');
    optsDiv.classList.add('quiz-options');
    qDiv.append(optsDiv);

    // По смене типа пересоздаём опции
    typeSelect.addEventListener('change', e => {
    question.type = e.target.value;          // либо "single", либо "multiple"
    question.answer = question.type === 'single' ? 0 : [];
    this._rerenderOptions(question, optsDiv);
    this._markSelectedRows(question, optsDiv);
    });

    // Первый рендер опций + подсветка
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
    qDiv.append(addOpt);

    container.append(qDiv);
  }

  _rerenderOptions(question, container) {
    container.innerHTML = '';
    question.options.forEach((optText, idx) => {
      const row = document.createElement('div');
      row.classList.add('quiz-option-row');

      // 1) радио или чекбокс
      const inpSel = document.createElement('input');
      inpSel.type = question.type === 'single' ? 'radio' : 'checkbox';
      inpSel.name = `quiz-${question.id}`;
      inpSel.checked = question.type === 'single'
        ? question.answer === idx
        : question.answer.includes(idx);
      inpSel.classList.add('quiz-option-select');
      inpSel.addEventListener('change', () => {
        if (question.type === 'single') {
          question.answer = idx;
        } else {
          const s = new Set(question.answer);
          inpSel.checked ? s.add(idx) : s.delete(idx);
          question.answer = Array.from(s);
        }
        this._markSelectedRows(question, container);
      });
      row.append(inpSel);

      // 2) текст варианта
      const inpText = document.createElement('input');
      inpText.type = 'text';
      inpText.placeholder = `Вариант ${idx + 1}`;
      inpText.value = optText;
      inpText.classList.add('quiz-option-text');
      inpText.addEventListener('input', e =>
        question.options[idx] = e.target.value
      );
      row.append(inpText);

      // 3) кнопка удаления опции
      const btnDel = document.createElement('button');
      btnDel.type = 'button';
      btnDel.textContent = '×';
      btnDel.classList.add('quiz-option-delete');
      btnDel.addEventListener('click', () => {
        question.options.splice(idx, 1);
        if (question.type === 'single' && question.answer >= idx) {
          question.answer = Math.max(0, question.answer - 1);
        }
        this._rerenderOptions(question, container);
        this._markSelectedRows(question, container);
      });
      row.append(btnDel);

      container.append(row);
    });
  }

  /**
   * После каждого изменения выделяет строки с правильными ответами
   */
  _markSelectedRows(question, container) {
    Array.from(container.children).forEach((row, i) => {
      const isSelected = question.type === 'single'
        ? question.answer === i
        : question.answer.includes(i);
      row.classList.toggle('selected', isSelected);
    });
  }

  save() {
    return this.data;
  }
}
