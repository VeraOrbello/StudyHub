// Dashboard JavaScript for StudyHub

document.addEventListener('DOMContentLoaded', () => {
    // Initialize components
    initTodoList();
    initQuickNotes();
    initCalendar();
    initAIAssistant();
    initFoldersCustomization();
    initQuickActions();

    animateStats();
});

// Animate stats numbers
function animateStats() {
    document.querySelectorAll('.stat-number[data-target]').forEach(counter => {
        const target = +counter.getAttribute('data-target');
        let count = 0;
        const increment = target / 50;
        const updateCount = () => {
            if (count < target) {
                count += increment;
                counter.innerText = Math.ceil(count);
                setTimeout(updateCount, 30);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });
}

// --- To-Do List ---

let todos = JSON.parse(localStorage.getItem('todos')) || [];

function initTodoList() {
    const todoList = document.getElementById('todoList');
    if (!todoList) return;
    todoList.innerHTML = '';

    if (todos.length === 0) {
        const defaultTasks = [
            'Complete math assignment',
            'Read chapter 5',
            'Prepare for quiz',
            'Review notes'
        ];
        defaultTasks.forEach(task => addTodoItem(task, false, Date.now()));
    } else {
        todos.forEach(todo => addTodoItem(todo.text, todo.completed, todo.id));
    }

    document.getElementById('addTodoBtn')?.addEventListener('click', addTodo);
    document.getElementById('todoInput')?.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTodo();
        }
    });
}

function addTodo() {
    const input = document.getElementById('todoInput');
    if (!input) return;
    const text = input.value.trim();
    if (!text) return;
    const id = Date.now();
    addTodoItem(text, false, id);
    todos.push({ id, text, completed: false });
    saveTodos();
    input.value = '';
}


function addTodoItem(text, completed, id) {
    const todoList = document.getElementById('todoList');
    if (!todoList) return;

    if (todoList.querySelector('.empty-state')) {
        todoList.innerHTML = '';
    }

    const div = document.createElement('div');
    div.className = `todo-item${completed ? ' completed' : ''}`;
    div.setAttribute('data-todo-id', id);

    div.innerHTML = `
        <div class="todo-checkbox${completed ? ' checked' : ''}" onclick="toggleTodo(${id})"></div>
        <div class="todo-text" tabindex="0" role="textbox" aria-label="To-do task" onkeypress="handleTodoKeyPress(event, ${id})" onblur="finishEditingTodo(${id})" ondblclick="editTodoStart(${id})">${escapeHtml(text)}</div>
        <div class="todo-actions">
            <button class="todo-btn delete" onclick="deleteTodo(${id})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;

    todoList.appendChild(div);
}

// Start editing todo text
function editTodoStart(id) {
    const todoItem = document.querySelector(`.todo-item[data-todo-id="${id}"]`);
    if (!todoItem) return;
    const todoText = todoItem.querySelector('.todo-text');
    if (!todoText) return;
    todoText.setAttribute('contenteditable', 'true');
    todoText.focus();
    placeCaretAtEnd(todoText);
}

// Handle keypress on editable todo text (save on Enter)
function handleTodoKeyPress(event, id) {
    if (event.key === 'Enter') {
        event.preventDefault();
        finishEditingTodo(id);
    }
}

// Finish editing todo text
function finishEditingTodo(id) {
    const todoItem = document.querySelector(`.todo-item[data-todo-id="${id}"]`);
    if (!todoItem) return;
    const todoText = todoItem.querySelector('.todo-text');
    if (!todoText) return;

    todoText.removeAttribute('contenteditable');

    let newText = todoText.textContent.trim();
    if (!newText) {
        deleteTodo(id);
        return;
    }

    const index = todos.findIndex(t => t.id === id);
    if (index !== -1) {
        todos[index].text = newText;
        saveTodos();
        todoText.textContent = newText;
    }
}

// Move caret to end of contenteditable element
function placeCaretAtEnd(el) {
    el.focus();
    if (typeof window.getSelection !== "undefined" && typeof document.createRange !== "undefined") {
        const range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }
}

// Escape HTML to prevent XSS in todo text display
function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function toggleTodo(id) {
    const index = todos.findIndex(t => t.id === id);
    if (index === -1) return;

    const todoItem = document.querySelector(`.todo-item[data-todo-id="${id}"]`);
    if (!todoItem) return;

    const checkbox = todoItem.querySelector('.todo-checkbox');
    const completed = !todos[index].completed;

    todos[index].completed = completed;
    saveTodos();

    if (completed) {
        todoItem.classList.add('completed');
        checkbox.classList.add('checked');
    } else {
        todoItem.classList.remove('completed');
        checkbox.classList.remove('checked');
    }
}

function deleteTodo(id) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    const todoItem = document.querySelector(`.todo-item[data-todo-id="${id}"]`);
    if (todoItem) todoItem.remove();

    todos = todos.filter(t => t.id !== id);
    saveTodos();

    const todoList = document.getElementById('todoList');
    if (todoList && todoList.children.length === 0) {
        todoList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <p>No tasks yet. Add one above!</p>
            </div>`;
    }
}

function saveTodos() {
    localStorage.setItem('todos', JSON.stringify(todos));
}

// --- Quick Notes ---

let quickNotes = JSON.parse(localStorage.getItem('quickNotes')) || [];

function initQuickNotes() {
    const quickNotesList = document.getElementById('quickNotesList');
    if (!quickNotesList) return;
    quickNotesList.innerHTML = '';
    if (quickNotes.length === 0) {
        quickNotesList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <p>No quick notes yet. Add one above!</p>
            </div>`;
    } else {
        quickNotes.forEach(note => addQuickNoteItem(note.id, note.content, note.date));
    }

    document.getElementById('addQuickNoteBtn')?.addEventListener('click', addQuickNote);
    document.getElementById('quickNoteInput')?.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addQuickNote();
        }
    });
}

function addQuickNote() {
    const input = document.getElementById('quickNoteInput');
    if (!input) return;
    const text = input.value.trim();
    if (!text) return;
    const id = Date.now();
    const date = new Date().toLocaleString();
    addQuickNoteItem(id, text, date);
    quickNotes.push({ id, content: text, date });
    saveQuickNotes();
    input.value = '';
}

function addQuickNoteItem(id, content, date) {
    const quickNotesList = document.getElementById('quickNotesList');
    if (!quickNotesList) return;

    if (quickNotesList.querySelector('.empty-state')) {
        quickNotesList.innerHTML = '';
    }

    const div = document.createElement('div');
    div.className = 'note-item';
    div.setAttribute('data-note-id', id);
    div.innerHTML = `
        <div class="note-content">
            <div class="note-date">${date}</div>
            <div>${content}</div>
        </div>
        <div class="note-actions">
            <button class="note-btn edit" onclick="editQuickNote(${id})">
                <i class="fas fa-edit"></i>
            </button>
            <button class="note-btn delete" onclick="deleteQuickNote(${id})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;

    quickNotesList.appendChild(div);
}

function editQuickNote(id) {
    const note = quickNotes.find(n => n.id === id);
    if (!note) return;
    const newContent = prompt('Edit note:', note.content);
    if (newContent !== null) {
        note.content = newContent.trim();
        if (!note.content) {
            deleteQuickNote(id);
            return;
        }
        const noteElement = document.querySelector(`.note-item[data-note-id="${id}"] .note-content div:last-child`);
        if (noteElement) noteElement.textContent = note.content;
        saveQuickNotes();
    }
}

function deleteQuickNote(id) {
    if (!confirm('Are you sure you want to delete this note?')) return;
    const noteElement = document.querySelector(`.note-item[data-note-id="${id}"]`);
    if (noteElement) noteElement.remove();
    quickNotes = quickNotes.filter(n => n.id !== id);
    saveQuickNotes();

    const quickNotesList = document.getElementById('quickNotesList');
    if (quickNotesList && quickNotesList.children.length === 0) {
        quickNotesList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <p>No quick notes yet. Add one above!</p>
            </div>`;
    }
}

function saveQuickNotes() {
    localStorage.setItem('quickNotes', JSON.stringify(quickNotes));
}

// --- Calendar ---

let calendarDate = new Date();

function initCalendar() {
    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            calendarDate.setMonth(calendarDate.getMonth() -1);
            renderCalendar();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            calendarDate.setMonth(calendarDate.getMonth() +1);
            renderCalendar();
        });
    }

    renderCalendar();

    // Initialize overlay calendar
    initOverlayCalendar();
}

function initOverlayCalendar() {
    const prevBtnOverlay = document.getElementById('prevMonthOverlay');
    const nextBtnOverlay = document.getElementById('nextMonthOverlay');

    if (prevBtnOverlay) {
        prevBtnOverlay.addEventListener('click', () => {
            calendarDate.setMonth(calendarDate.getMonth() -1);
            renderOverlayCalendar();
        });
    }

    if (nextBtnOverlay) {
        nextBtnOverlay.addEventListener('click', () => {
            calendarDate.setMonth(calendarDate.getMonth() +1);
            renderOverlayCalendar();
        });
    }

    renderOverlayCalendar();
}

async function renderCalendar() {
    const calendarEl = document.getElementById('calendar');
    const monthYearEl = document.getElementById('currentMonthYear');

    if (!calendarEl || !monthYearEl) return;

    const year = calendarDate.getFullYear();
    const month = calendarDate.getMonth() + 1;  // month 1-12

    monthYearEl.textContent = calendarDate.toLocaleString('default', {month: 'long', year: 'numeric'});

    let data;
    try {
        const response = await fetch(`api/calendar.php?year=${year}&month=${month}`);
        if (!response.ok) throw new Error('Network response was not ok');
        data = await response.json();
    } catch(e) {
        console.error('Failed to fetch calendar data:', e);
        calendarEl.innerHTML = '<p class="error">Could not load calendar data.</p>';
        return;
    }

    // Calendar data format: data.calendar is an object keyed by day number with array of schedule entries
    const calendar = data.calendar || {};

    // Clear previous calendar
    calendarEl.innerHTML = '';

    // Days of week header
    const daysOfWeek = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const headerEl = document.createElement('div');
    headerEl.classList.add('calendar-grid','calendar-header-row');
    daysOfWeek.forEach(day => {
        const dayEl = document.createElement('div');
        dayEl.classList.add('calendar-day-header');
        dayEl.textContent = day;
        headerEl.appendChild(dayEl);
    });
    calendarEl.appendChild(headerEl);

    // Prepare calendar grid container
    const gridEl = document.createElement('div');
    gridEl.classList.add('calendar-grid','calendar-days-grid');

    // Starting day for first of month (0=Sun,..6=Sat)
    const firstDay = new Date(year, month - 1, 1).getDay();
    const daysInMonth = new Date(year, month, 0).getDate();
    const todayStr = new Date().toISOString().slice(0,10);

    // Fill preceding empty days
    for(let i=0; i<firstDay; i++) {
        const emptyEl = document.createElement('div');
        emptyEl.classList.add('calendar-day','empty');
        gridEl.appendChild(emptyEl);
    }

    for(let d=1; d<=daysInMonth; d++) {
        const dayEl = document.createElement('div');
        dayEl.classList.add('calendar-day');
        dayEl.textContent = d;

        const dayISO = new Date(year, month -1, d).toISOString().slice(0,10);

        if(dayISO === todayStr) {
            dayEl.classList.add('today');
        }

        // Mark day with scheduled classes if calendar[d] has entries
        if(Array.isArray(calendar[d]) && calendar[d].length > 0) {
            dayEl.classList.add('has-classes');
            const indicator = document.createElement('div');
            indicator.classList.add('class-indicator');
            dayEl.appendChild(indicator);
            dayEl.title = `${calendar[d].length} scheduled class${calendar[d].length > 1 ? 'es' : ''}`;
        }

        // Future: Add click handler for day to show details if needed

        gridEl.appendChild(dayEl);
    }

    calendarEl.appendChild(gridEl);
}

// --- Folder Customization ---

let currentFolderId = null;

function openCustomizeModal(folderId) {
    const modal = document.getElementById('customizeModal');
    if (!modal) return;

    currentFolderId = folderId;

    // Reset inputs and visible groups
    document.getElementById('nameInputGroup').style.display = 'none';
    document.getElementById('urlInputGroup').style.display = 'none';
    document.getElementById('folderNameInput').value = '';

    modal.style.display = 'flex';
}

// Folder click to open subject modal with sample data
function openSubjectModal(folderName, folderImage) {
    const modal = document.getElementById('subjectModal');
    if (!modal) return;

    // Set subject modal title and icon
    const titleEl = document.getElementById('subjectModalTitle');
    const iconEl = document.getElementById('subjectModalIcon');
    if(titleEl) titleEl.textContent = folderName;
    if(iconEl) iconEl.src = folderImage;

    // Reset notes, assignments, schedule lists to placeholder
    document.getElementById('notesList').innerHTML = `
        <div class="empty-state">
            <i class="fas fa-sticky-note"></i>
            <p>No notes yet. Start typing above to create your first note!</p>
        </div>`;
    document.getElementById('assignmentsList').innerHTML = `
        <div class="empty-state">
            <i class="fas fa-tasks"></i>
            <p>No assignments for this subject.</p>
        </div>`;
    document.getElementById('scheduleList').innerHTML = `
        <div class="empty-state">
            <i class="fas fa-clock"></i>
            <p>No classes scheduled for today.</p>
        </div>`;

    // Show modal
    modal.style.display = 'flex';
}

function closeSubjectModal() {
    const modal = document.getElementById('subjectModal');
    if (!modal) return;

    modal.style.display = 'none';
}

// Initialize folder clicks and customize button clicks
function initFolders() {
    // Folder anchor clicks open subject modal
    document.querySelectorAll('.custom-folder-wrapper a.custom-folder').forEach(anchor => {
        anchor.addEventListener('click', e => {
            e.preventDefault();

            const subjectId = anchor.getAttribute('data-subject-id');
            const folderName = anchor.getAttribute('data-folder-name');
            const folderImage = anchor.getAttribute('data-folder-image');

            openSubjectModal(subjectId, folderName, folderImage);
        });
    });

    // Customize button clicks open customize modal
    document.querySelectorAll('.custom-folder-wrapper .customize-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();

            const folderId = btn.getAttribute('data-folder-id');
            openCustomizeModal(folderId);
        });
    });

    // Modal close buttons and click outside to close for customizeModal
    const customizeModal = document.getElementById('customizeModal');
    if(customizeModal) {
        customizeModal.querySelector('.modal-close')?.addEventListener('click', () => {
            customizeModal.style.display = 'none';
            resetModalInputs();
        });

        customizeModal.addEventListener('click', e => {
            if(e.target === customizeModal) {
                customizeModal.style.display = 'none';
                resetModalInputs();
            }
        });
    }

    // Modal close for subject modal
    const subjectModal = document.getElementById('subjectModal');
    if(subjectModal) {
        subjectModal.querySelector('.subject-modal-close')?.addEventListener('click', closeSubjectModal);
        subjectModal.addEventListener('click', e => {
            if (e.target === subjectModal) closeSubjectModal();
        });
    }
}

// --- Subject Modal Functions ---

let timerInterval;
let timerSeconds = 25 * 60;
let isTimerRunning = false;
let notes = [];
let currentEditingNoteId = null;

function openSubjectModal(folderName, folderImage) {
    const modal = document.getElementById('subjectModal');
    if (!modal) return;

    document.getElementById('subjectModalTitle').textContent = folderName;
    document.getElementById('subjectModalIcon').src = folderImage;

    resetModalData();
    loadSampleData(folderName);

    modal.style.display = 'flex';
}

function closeSubjectModal() {
    const modal = document.getElementById('subjectModal');
    if (!modal) return;
    modal.style.display = 'none';
    pauseTimer();
    resetModalData();
}

function resetModalData() {
    notes = [];
    document.getElementById('notesList').innerHTML = `
        <div class="empty-state">
            <i class="fas fa-sticky-note"></i>
            <p>No notes yet. Start typing above to create your first note!</p>
        </div>
    `;
    document.querySelector('.note-editor').innerHTML = '';

    document.getElementById('assignmentsList').innerHTML = `
        <div class="empty-state">
            <i class="fas fa-tasks"></i>
            <p>No assignments for this subject.</p>
        </div>
    `;

    document.getElementById('scheduleList').innerHTML = `
        <div class="empty-state">
            <i class="fas fa-clock"></i>
            <p>No classes scheduled for today.</p>
        </div>
    `;

    resetTimer();
}

function loadSampleData(subjectName) {
    const sampleAssignments = [
        { id: 1, title: 'Chapter 5 Exercises', dueDate: '2024-01-15', status: 'Not Started' },
        { id: 2, title: 'Research Paper', dueDate: '2024-01-20', status: 'In Progress' },
        { id: 3, title: 'Weekly Quiz', dueDate: '2024-01-12', status: 'Done' }
    ];

    const sampleSchedule = [
        { classType: 'Lecture', location: 'Room 301', startTime: '09:00', endTime: '10:30' },
        { classType: 'Lab', location: 'Science Building', startTime: '14:00', endTime: '16:00' }
    ];

    const assignmentsList = document.getElementById('assignmentsList');
    if (assignmentsList.querySelector('.empty-state')) {
        assignmentsList.innerHTML = '';
        sampleAssignments.forEach(assignment => {
            const assignmentItem = createAssignmentItem(assignment);
            assignmentsList.appendChild(assignmentItem);
        });
    }

    const scheduleList = document.getElementById('scheduleList');
    if (scheduleList.querySelector('.empty-state')) {
        scheduleList.innerHTML = '';
        sampleSchedule.forEach(schedule => {
            const scheduleItem = createScheduleItem(schedule);
            scheduleList.appendChild(scheduleItem);
        });
    }
}

function createAssignmentItem(assignment) {
    const div = document.createElement('div');
    div.className = 'assignment-item';

    const statusClass = `status-${assignment.status.toLowerCase().replace(' ', '-')}`;

    div.innerHTML = `
        <div class="assignment-info">
            <h4>${assignment.title}</h4>
            <p>Due: ${assignment.dueDate}</p>
        </div>
        <div class="assignment-status">
            <button class="status-badge ${statusClass}" onclick="updateAssignmentStatus(this, ${assignment.id})">
                ${assignment.status}
            </button>
        </div>
    `;

    return div;
}

function createScheduleItem(schedule) {
    const div = document.createElement('div');
    div.className = 'schedule-item';

    div.innerHTML = `
        <div class="schedule-details">
            <h4>${schedule.classType}</h4>
            <p>${schedule.location}</p>
        </div>
        <div class="schedule-time">
            ${schedule.startTime} - ${schedule.endTime}
        </div>
    `;

    return div;
}

// Timer functionality
function updateTimerDisplay() {
    const minutes = Math.floor(timerSeconds / 60);
    const seconds = timerSeconds % 60;
    document.getElementById('timerDisplay').textContent =
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function startTimer() {
    if (!isTimerRunning) {
        isTimerRunning = true;
        timerInterval = setInterval(() => {
            timerSeconds--;
            updateTimerDisplay();

            if (timerSeconds <= 0) {
                clearInterval(timerInterval);
                isTimerRunning = false;
                alert('Study session completed! 🎉');
                timerSeconds = 25 * 60;
                updateTimerDisplay();
            }
        }, 1000);
    }
}

function pauseTimer() {
    clearInterval(timerInterval);
    isTimerRunning = false;
}

function resetTimer() {
    clearInterval(timerInterval);
    isTimerRunning = false;
    timerSeconds = 25 * 60;
    updateTimerDisplay();
}

function editNote(noteId) {
    const note = notes.find(n => n.id === noteId);
    if (note) {
        currentEditingNoteId = noteId;
        document.getElementById('editNoteTextarea').value = note.content;
        document.getElementById('editNoteModal').style.display = 'flex';
    }
}

function saveEditedNote() {
    const editedContent = document.getElementById('editNoteTextarea').value.trim();
    if (editedContent) {
        const noteIndex = notes.findIndex(note => note.id === currentEditingNoteId);
        if (noteIndex !== -1) {
            notes[noteIndex].content = editedContent;

            const noteElement = document.querySelector(`[data-note-id="${currentEditingNoteId}"] .note-content`);
            if (noteElement) {
                noteElement.innerHTML = editedContent;
            }
        }

        const quickNoteIndex = quickNotes.findIndex(note => note.id === currentEditingNoteId);
        if (quickNoteIndex !== -1) {
            quickNotes[quickNoteIndex].content = editedContent;
            saveQuickNotes();

            const quickNoteElement = document.querySelector(`[data-note-id="${currentEditingNoteId}"] .note-content div:last-child`);
            if (quickNoteElement) {
                quickNoteElement.textContent = editedContent;
            }
        }

        document.getElementById('editNoteModal').style.display = 'none';
        currentEditingNoteId = null;
    } else {
        alert('Note content cannot be empty');
    }
}

function deleteNote(noteId) {
    if (!confirm('Are you sure you want to delete this note?')) return;

    const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
    if (noteElement) noteElement.remove();

    notes = notes.filter(note => note.id !== noteId);
    quickNotes = quickNotes.filter(note => note.id !== noteId);
    saveQuickNotes();

    const notesList = document.getElementById('notesList');
    if (notesList && notesList.children.length === 0) {
        notesList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <p>No notes yet. Start typing above to create your first note!</p>
            </div>
        `;
    }

    const quickNotesList = document.getElementById('quickNotesList');
    if (quickNotesList && quickNotes.length === 0) {
        quickNotesList.innerHTML = `
            <div class="notes-empty">
                <i class="fas fa-sticky-note"></i>
                <p>No quick notes yet. Add one above!</p>
            </div>
        `;
    }
}

function updateAssignmentStatus(element, assignmentId) {
    const currentStatus = element.textContent.trim();
    let newStatus;

    switch (currentStatus) {
        case 'Not Started':
            newStatus = 'In Progress';
            break;
        case 'In Progress':
            newStatus = 'Done';
            break;
        case 'Done':
            newStatus = 'Not Started';
            break;
        default:
            newStatus = 'Not Started';
    }

    element.textContent = newStatus;
    element.className = `status-badge status-${newStatus.toLowerCase().replace(' ', '-')}`;

    console.log(`Assignment ${assignmentId} status updated to: ${newStatus}`);
}

// --- Folder Customization Modal Functions ---

function showNameInput() {
    document.getElementById('nameInputGroup').style.display = 'block';
    document.getElementById('urlInputGroup').style.display = 'none';
}

function hideNameInput() {
    document.getElementById('nameInputGroup').style.display = 'none';
    document.getElementById('folderNameInput').value = '';
}

function showUrlInput() {
    document.getElementById('urlInputGroup').style.display = 'block';
    document.getElementById('nameInputGroup').style.display = 'none';
}

function hideUrlInput() {
    document.getElementById('urlInputGroup').style.display = 'none';
    document.getElementById('imageUrl').value = '';
}

function triggerFileUpload() {
    const fileInput = document.querySelector(`.folderUpload[data-folder-id="${currentFolderId}"]`);
    if (fileInput) {
        fileInput.click();
        document.getElementById('customizeModal').style.display = 'none';
    }
}

function saveName() {
    const newName = document.getElementById('folderNameInput').value.trim();
    if (newName) {
        const nameElement = document.getElementById(`folderName${currentFolderId}`);
        if (nameElement) {
            nameElement.textContent = newName;
            saveFolderName(currentFolderId, newName);
        }
        document.getElementById('customizeModal').style.display = 'none';
        document.getElementById('folderNameInput').value = '';
    } else {
        alert('Please enter a folder name');
    }
}

function saveUrl() {
    const url = document.getElementById('imageUrl').value.trim();
    if (url) {
        const img = document.getElementById(`folderImg${currentFolderId}`);
        if (img) {
            img.src = url;
            saveFolderImage(currentFolderId, url);
        }
        document.getElementById('customizeModal').style.display = 'none';
        document.getElementById('imageUrl').value = '';
    } else {
        alert('Please enter a valid URL');
    }
}

function handleFileUpload(e) {
    const folderId = e.target.getAttribute('data-folder-id');
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById(`folderImg${folderId}`);
        if (img) {
            img.src = e.target.result;
            saveFolderImage(folderId, e.target.result);
        }
    };
    reader.readAsDataURL(file);
}

function saveFolderImage(folderId, imageSrc) {
    const formData = new FormData();
    formData.append('action', 'save_folder_image');
    formData.append('folder_id', folderId);
    formData.append('image_data', imageSrc);

    fetch('', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Folder image saved successfully');
            } else {
                console.error('Failed to save folder image');
            }
        })
        .catch(error => {
            console.error('Error saving folder image:', error);
        });
}

function saveFolderName(folderId, folderName) {
    const formData = new FormData();
    formData.append('action', 'save_folder_name');
    formData.append('folder_id', folderId);
    formData.append('folder_name', folderName);

    fetch('', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Folder name saved successfully');
            } else {
                console.error('Failed to save folder name');
            }
        })
        .catch(error => {
            console.error('Error saving folder name:', error);
        });
}

function resetModalInputs() {
    hideNameInput();
    hideUrlInput();
}

// --- Form Modal Functions ---

function initFormModals() {
    // Quick Actions Modals
    document.getElementById('addAssignmentAction')?.addEventListener('click', function () {
        document.getElementById('addAssignmentModal').style.display = 'flex';
    });

    document.getElementById('addTaskAction')?.addEventListener('click', function () {
        document.getElementById('addTaskModal').style.display = 'flex';
    });

    document.getElementById('addSubjectAction')?.addEventListener('click', function () {
        document.getElementById('addSubjectModal').style.display = 'flex';
    });

    document.getElementById('addScheduleAction')?.addEventListener('click', function () {
        alert('Add Schedule functionality - to be implemented');
    });

    // Close form modals
    document.querySelectorAll('.form-modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function () {
            this.closest('.form-modal').style.display = 'none';
        });
    });

    document.getElementById('cancelAssignment')?.addEventListener('click', function () {
        document.getElementById('addAssignmentModal').style.display = 'none';
    });

    document.getElementById('cancelTask')?.addEventListener('click', function () {
        document.getElementById('addTaskModal').style.display = 'none';
    });

    document.getElementById('cancelSubject')?.addEventListener('click', function () {
        document.getElementById('addSubjectModal').style.display = 'none';
    });

    // Form submissions
    document.getElementById('assignmentForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const title = document.getElementById('assignmentTitle').value;
        const subject = document.getElementById('assignmentSubject').value;
        const dueDate = document.getElementById('assignmentDueDate').value;
        const description = document.getElementById('assignmentDescription').value;

        console.log('Adding assignment:', { title, subject, dueDate, description });
        alert('Assignment added successfully!');
        document.getElementById('addAssignmentModal').style.display = 'none';
        this.reset();
    });

    document.getElementById('taskForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const title = document.getElementById('taskTitle').value;
        const priority = document.getElementById('taskPriority').value;
        const dueDate = document.getElementById('taskDueDate').value;
        const description = document.getElementById('taskDescription').value;

        addTodoItem(title, false, Date.now());
        document.getElementById('addTaskModal').style.display = 'none';
        this.reset();
    });

    document.getElementById('subjectForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const name = document.getElementById('subjectName').value;
        const code = document.getElementById('subjectCode').value;
        const color = document.getElementById('subjectColor').value;
        const description = document.getElementById('subjectDescription').value;

        console.log('Adding subject:', { name, code, color, description });
        alert('Subject added successfully!');
        document.getElementById('addSubjectModal').style.display = 'none';
        this.reset();
    });

    // Close modals when clicking outside
    document.querySelectorAll('.form-modal').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
}

// --- Subject Modal Note Functions ---

function initSubjectModalNotes() {
    document.getElementById('saveNote')?.addEventListener('click', function () {
        const noteContent = document.querySelector('.note-editor').innerHTML.trim();
        if (noteContent && noteContent !== '<br>') {
            const notesList = document.getElementById('notesList');

            if (notesList.querySelector('.empty-state')) {
                notesList.innerHTML = '';
            }

            const noteItem = document.createElement('div');
            noteItem.className = 'note-item';
            const noteId = Date.now();
            noteItem.innerHTML = `
                <div class="note-date">${new Date().toLocaleString()}</div>
                <div class="note-content">${noteContent}</div>
                <div class="note-actions">
                    <button class="note-btn edit" onclick="editNote(${noteId})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="note-btn delete" onclick="deleteNote(${noteId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            noteItem.setAttribute('data-note-id', noteId);
            notesList.appendChild(noteItem);

            notes.push({
                id: noteId,
                content: noteContent,
                date: new Date().toLocaleString()
            });

            document.querySelector('.note-editor').innerHTML = '';
        } else {
            alert('Please write something in the note editor first!');
        }
    });

    // Quick actions in subject modal
    document.getElementById('addAssignmentBtn')?.addEventListener('click', function () {
        alert('Redirecting to Add Assignment page...');
    });

    document.getElementById('addScheduleBtn')?.addEventListener('click', function () {
        alert('Redirecting to Add Class page...');
    });

    document.getElementById('viewAllNotesBtn')?.addEventListener('click', function () {
        alert('Showing all notes for this subject...');
    });

    document.getElementById('studyMaterialsBtn')?.addEventListener('click', function () {
        alert('Opening study materials...');
    });
}

// --- Edit Note Modal ---

function initEditNoteModal() {
    document.querySelector('.edit-note-close')?.addEventListener('click', function () {
        document.getElementById('editNoteModal').style.display = 'none';
    });

    document.getElementById('cancelEditNote')?.addEventListener('click', function () {
        document.getElementById('editNoteModal').style.display = 'none';
    });

    document.getElementById('saveEditedNote')?.addEventListener('click', saveEditedNote);

    document.getElementById('editNoteModal')?.addEventListener('click', function (e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
}

// --- Initialization ---

document.addEventListener('DOMContentLoaded', () => {
    initCalendar();
    initFolders();
    animateStats();
    initTodoList();
    initQuickNotes();
    initAIAssistant();
    initQuickActions();
    initFoldersCustomization();
    initFormModals();
    initSubjectModalNotes();
    initEditNoteModal();

    // Timer controls
    document.getElementById('timerStart')?.addEventListener('click', startTimer);
    document.getElementById('timerPause')?.addEventListener('click', pauseTimer);
    document.getElementById('timerReset')?.addEventListener('click', resetTimer);

    // Initialize timer display
    updateTimerDisplay();

    // Update folder links to open modal instead
    document.querySelectorAll('.custom-folder').forEach(folder => {
        folder.addEventListener('click', function (e) {
            e.preventDefault();
            const folderName = this.getAttribute('data-folder-name');
            const folderImage = this.getAttribute('data-folder-image');

            openSubjectModal(folderName, folderImage);
        });
    });

    // Modal close for subject modal
    const subjectModal = document.getElementById('subjectModal');
    if (subjectModal) {
        subjectModal.querySelector('.subject-modal-close')?.addEventListener('click', closeSubjectModal);
        subjectModal.addEventListener('click', function (e) {
            if (e.target === this) closeSubjectModal();
        });
    }
});

async function renderCalendar() {
    const calendarEl = document.getElementById('calendar');
    const monthYearEl = document.getElementById('currentMonthYear');
    if (!calendarEl || !monthYearEl) return;

    const year = calendarDate.getFullYear();
    const month = calendarDate.getMonth();

    monthYearEl.textContent = calendarDate.toLocaleString('default', { month: 'long', year: 'numeric' });

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startDay = firstDay.getDay();

    // Fetch calendar events from API
    let events = [];
    try {
        const response = await fetch(`api/calendar.php?year=${year}&month=${month + 1}`);
        if (response.ok) {
            events = await response.json();
        }
    } catch (error) {
        console.error('Failed to load calendar events:', error);
    }

    // Clear previous calendar
    calendarEl.innerHTML = '';

    // Add day headers
    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const headerRow = document.createElement('div');
    headerRow.className = 'calendar-grid';
    daysOfWeek.forEach(day => {
        const dayHeader = document.createElement('div');
        dayHeader.className = 'calendar-day-header';
        dayHeader.textContent = day;
        headerRow.appendChild(dayHeader);
    });
    calendarEl.appendChild(headerRow);

    // Create grid container for days
    const daysGrid = document.createElement('div');
    daysGrid.className = 'calendar-grid';

    // Add empty boxes for days before first day
    for (let i = 0; i < startDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'calendar-day empty';
        daysGrid.appendChild(emptyCell);
    }

    const todayStr = new Date().toISOString().split('T')[0];

    for (let day = 1; day <= daysInMonth; day++) {
        const dayDate = new Date(year, month, day);
        const dayStr = dayDate.toISOString().split('T')[0];

        const dayCell = document.createElement('div');
        dayCell.className = 'calendar-day';
        if (dayStr === todayStr) dayCell.classList.add('today');

        // Check if this day has events
        const hasEvent = events.some(ev => ev.date === dayStr);
        if (hasEvent) dayCell.classList.add('has-classes');

        dayCell.textContent = day;

        // If has events, add indicator dot and tooltip
        if (hasEvent) {
            const indicator = document.createElement('div');
            indicator.className = 'class-indicator';
            dayCell.appendChild(indicator);
            dayCell.title = 'Has scheduled classes';
        }

        daysGrid.appendChild(dayCell);
    }

    calendarEl.appendChild(daysGrid);
}

// --- AI Assistant ---

function initAIAssistant() {
    const askBtn = document.getElementById('askAI');
    const aiInput = document.getElementById('aiQuestion');
    if (!askBtn || !aiInput) return;

    askBtn.addEventListener('click', sendAIQuestion);
    aiInput.addEventListener('keypress', e => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendAIQuestion();
        }
    });

    // Quick suggestions
    document.querySelectorAll('.quick-question, .suggestion').forEach(el => {
        el.addEventListener('click', () => {
            const question = el.getAttribute('data-question');
            if (!question) return;
            aiInput.value = question;
            sendAIQuestion();
        });
    });

    document.getElementById('clearChat')?.addEventListener('click', clearAIChat);
    document.getElementById('exportChat')?.addEventListener('click', exportAIChat);

    // Add initial greeting if empty
    const chatContainer = document.getElementById('aiChatContainer');
    if (chatContainer && chatContainer.children.length === 0) {
        addAIMessage('assistant', "Hello! I'm your advanced AI study assistant. How can I help you today?");
    }
}

async function sendAIQuestion() {
    const aiInput = document.getElementById('aiQuestion');
    const question = aiInput.value.trim();
    const chatContainer = document.getElementById('aiChatContainer');
    const askBtn = document.getElementById('askAI');

    if (!question) {
        alert('Please enter a question');
        return;
    }

    // Add user message
    addAIMessage('user', question);

    aiInput.value = '';
    if (askBtn) {
        askBtn.disabled = true;
        askBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    try {
        // TODO: Replace with actual backend AI call
        const aiResponse = await simulateAIResponse(question);

        addAIMessage('assistant', aiResponse);
    } catch (error) {
        console.error('AI assistant error:', error);
        addAIMessage('assistant', "Sorry, I couldn't process your request. Please try again.");
    } finally {
        if (askBtn) {
            askBtn.disabled = false;
            askBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
    }
}

function addAIMessage(role, text) {
    const chatContainer = document.getElementById('aiChatContainer');
    if (!chatContainer) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = `ai-message ai-${role}-message`;

    const avatarIcon = role === 'user' ? 'fas fa-user' : 'fas fa-robot';
    const senderName = role === 'user' ? 'You' : 'AI Assistant';

    const formattedText = formatAIText(text);

    messageDiv.innerHTML = `
        <div class="message-avatar"><i class="${avatarIcon}"></i></div>
        <div class="message-content">
            <div class="message-sender">${senderName}</div>
            <div class="message-text">${formattedText}</div>
            <div class="message-time">${new Date().toLocaleTimeString()}</div>
        </div>
    `;

    chatContainer.appendChild(messageDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function formatAIText(text) {
    if (!text) return '';
    // Simple markdown-like formatting: **bold**, `code`, line breaks
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/\n/g, '<br>');
}

function clearAIChat() {
    const chatContainer = document.getElementById('aiChatContainer');
    if (!chatContainer) return;

    if (confirm('Clear all chat messages?')) {
        chatContainer.innerHTML = '';
        addAIMessage('assistant', "Hello! I'm your advanced AI study assistant. How can I help you today?");
    }
}

function exportAIChat() {
    const chatContainer = document.getElementById('aiChatContainer');
    if (!chatContainer) return;
    let text = '';
    chatContainer.querySelectorAll('.ai-message').forEach(msg => {
        const sender = msg.querySelector('.message-sender')?.textContent || '';
        const content = msg.querySelector('.message-text')?.textContent || '';
        text += `${sender}: ${content}\n\n`;
    });

    const blob = new Blob([text], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'ai_chat.txt';
    a.click();
    URL.revokeObjectURL(a.href);
}

// Simulated AI response function (replace this with real backend call)
function simulateAIResponse(question) {
    return new Promise(resolve => {
        setTimeout(() => {
            resolve("This is a simulated response to: " + question);
        }, 1000);
    });
}

// --- Folder customization ---

function initFoldersCustomization() {
    // Open customize modal on button click
    document.querySelectorAll('.customize-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            openCustomizeModal(btn.getAttribute('data-folder-id'));
        });
    });

    // Modal close button
    const modal = document.getElementById('customizeModal');
    const closeBtn = modal.querySelector('.modal-close');
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        resetModalInputs();
    });

    // Modal option buttons
    document.getElementById('nameOption').addEventListener('click', () => {
        showNameInput();
    });
    document.getElementById('uploadOption').addEventListener('click', () => {
        triggerFileUpload();
    });
    document.getElementById('urlOption').addEventListener('click', () => {
        showUrlInput();
    });

    // Name input buttons
    document.getElementById('saveName').addEventListener('click', saveName);
    document.getElementById('cancelName').addEventListener('click', () => {
        hideNameInput();
    });

    // URL input buttons
    document.getElementById('useUrl').addEventListener('click', saveUrl);
    document.getElementById('cancelUrl').addEventListener('click', () => {
        hideUrlInput();
    });

    // File input change
    document.querySelectorAll('.folderUpload').forEach(input => {
        input.addEventListener('change', handleFileUpload);
    });

    // Close modal when clicking outside modal content
    modal.addEventListener('click', e => {
        if (e.target === modal) {
            modal.style.display = 'none';
            resetModalInputs();
        }
    });
}


function openCustomizeModal(folderId) {
    const modal = document.getElementById('customizeModal');
    if (!modal) return;

    currentFolderId = folderId;

    // Reset inputs and visible groups
    if(document.getElementById('nameInputGroup')) document.getElementById('nameInputGroup').style.display = 'none';
    if(document.getElementById('urlInputGroup')) document.getElementById('urlInputGroup').style.display = 'none';
    if(document.getElementById('folderNameInput')) document.getElementById('folderNameInput').value = '';

    modal.style.display = 'flex';
}

// --- Quick Actions ---


function initQuickActions() {
    ['addAssignmentAction', 'addTaskAction', 'addSubjectAction', 'addScheduleAction'].forEach(id => {
        const btn = document.getElementById(id);
        if(btn) {
            if (id === 'addSubjectAction') {
                btn.addEventListener('click', addNewSubjectDemo);
            } else {
                btn.addEventListener('click', () => alert(id + ' is clicked - feature to be implemented.'));
            }
        }
    });

    // More realistic implementation should open modals or navigate to pages
}

// Demo function: add a new subject and corresponding folder dynamically
function addNewSubjectDemo() {
    const newSubject = {
        id: Date.now(),
        name: 'New Subject ' + new Date().toLocaleTimeString(),
        image: '' // default or empty image
    };

    const foldersGrid = document.querySelector('.folders-grid');
    if (!foldersGrid) return;

    const currentFolders = foldersGrid.querySelectorAll('.custom-folder-wrapper');
    if (currentFolders.length >= 6) {
        alert('Maximum 6 folders reached.');
        return;
    }

    const folderId = currentFolders.length + 1;

    const folderWrapper = document.createElement('div');
    folderWrapper.className = 'custom-folder-wrapper';

    const folderAnchor = document.createElement('a');
    folderAnchor.href = '#';
    folderAnchor.className = 'custom-folder';
    folderAnchor.setAttribute('aria-label', 'Folder ' + folderId);
    folderAnchor.setAttribute('data-subject-id', newSubject.id);
    folderAnchor.setAttribute('data-folder-name', newSubject.name);
    folderAnchor.setAttribute('data-folder-image', newSubject.image);

    const folderIcon = document.createElement('div');
    folderIcon.className = 'folder-icon';
    folderIcon.style.background = `var(--folder-bg-${folderId})`;

    if (newSubject.image) {
        const img = document.createElement('img');
        img.src = newSubject.image;
        img.alt = `Folder ${folderId} image`;
        folderIcon.appendChild(img);
    } else {
        const icon = document.createElement('i');
        icon.className = 'fas fa-folder';
        icon.style.color = '#cfcfcf';
        folderIcon.appendChild(icon);
    }

    const folderInfo = document.createElement('div');
    folderInfo.className = 'folder-info';

    const h4 = document.createElement('h4');
    h4.textContent = newSubject.name;

    folderInfo.appendChild(h4);

    const customizeBtn = document.createElement('button');
    customizeBtn.className = 'customize-btn';
    customizeBtn.setAttribute('data-folder-id', folderId);
    customizeBtn.setAttribute('aria-label', `Customize folder ${folderId}`);
    customizeBtn.innerHTML = '<i class="fas fa-cog"></i>';

    folderAnchor.appendChild(folderIcon);
    folderAnchor.appendChild(folderInfo);
    folderAnchor.appendChild(customizeBtn);

    folderWrapper.appendChild(folderAnchor);
    foldersGrid.appendChild(folderWrapper);

    // Attach event listeners for folder and customize button
    folderAnchor.addEventListener('click', e => {
        e.preventDefault();
        openSubjectModal(newSubject.id, newSubject.name, newSubject.image);
    });

    customizeBtn.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        openCustomizeModal(folderId);
    });
}


