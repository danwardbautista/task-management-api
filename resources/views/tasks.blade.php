<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <!-- Refractor this with a modern JS soon, this is just quick dev for show -->
    <!-- CDN fast dev -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-2xl font-bold text-gray-900">Task Management</h1>
                <div class="flex items-center space-x-4">
                    <span id="userWelcome" class="text-gray-600"></span>
                    <button id="logoutBtn" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- View Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button id="activeTasksTab" class="py-2 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                        Active Tasks
                    </button>
                    <button id="trashedTasksTab" class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Trash
                    </button>
                </nav>
            </div>
        </div>

        <!-- Main search and filtering section -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-64">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input 
                        type="text" 
                        id="search" 
                        placeholder="Search tasks..." 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="to-do">To Do</option>
                        <option value="in-progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                <div>
                    <label for="sortBy" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select id="sortBy" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="created_at">Created Date</option>
                        <option value="title">Title</option>
                    </select>
                </div>
                <div>
                    <label for="sortOrder" class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                    <select id="sortOrder" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="desc">Descending</option>
                        <option value="asc">Ascending</option>
                    </select>
                </div>
                <button id="searchBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Search
                </button>
                <button id="clearBtn" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    Clear
                </button>
            </div>
        </div>

        <!-- Add Task Button -->
        <div id="addTaskSection" class="mb-6">
            <button id="addTaskBtn" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                Add New Task
            </button>
        </div>

        <!-- Loading indicator with spinner -->
        <div id="loading" class="text-center py-8 hidden">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mb-2"></div>
            <div class="text-gray-600">Loading tasks...</div>
        </div>

        <!-- Error display container -->
        <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 hidden"></div>

        <!-- Tasks Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8"></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Actions</th>
                    </tr>
                </thead>
                <tbody id="tasksTable" class="bg-white divide-y divide-gray-200">
                    <!-- Tasks will be populated here -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="mt-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-700">
                        Showing <span id="pageInfo"></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label for="perPage" class="text-sm text-gray-700">Items per page:</label>
                        <select id="perPage" class="px-2 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="70">70</option>
                            <option value="100">100</option>
                            <option value="custom">Custom</option>
                        </select>
                        <input 
                            type="number" 
                            id="customPerPage" 
                            placeholder="Enter number" 
                            min="1" 
                            max="100" 
                            class="px-2 py-1 border border-gray-300 rounded-md text-sm w-24 focus:outline-none focus:ring-2 focus:ring-blue-500 hidden"
                        >
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button id="prevPage" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 text-sm">
                        Previous
                    </button>
                    <div id="pageNumbers" class="flex space-x-1">
                        <!-- Page numbers will be populated here -->
                    </div>
                    <button id="nextPage" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 text-sm">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Task/Subtask create/edit modal overlay -->
    <div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl max-h-screen overflow-y-auto">
            <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add New Task</h2>
            
            <!-- Modal Error display container -->
            <div id="modalErrorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>
            
            <form id="taskForm" class="space-y-4">
                <input type="hidden" id="taskId">
                <input type="hidden" id="parentTaskId">
                <input type="hidden" id="isSubtask" value="false">
                
                <div>
                    <label for="taskTitle" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input 
                        type="text" 
                        id="taskTitle" 
                        name="title" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                
                <div>
                    <label for="taskContent" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea 
                        id="taskContent" 
                        name="content" 
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                </div>
                
                <div>
                    <label for="taskStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="taskStatus" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="to-do">To Do</option>
                        <option value="in-progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                
                <div id="taskStateContainer">
                    <label for="taskState" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <select id="taskState" name="task_state" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                
                <div>
                    <label for="taskImage" class="block text-sm font-medium text-gray-700 mb-1">Image (optional)</label>
                    <input 
                        type="file" 
                        id="taskImage" 
                        name="task_image" 
                        accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <!-- Current image preview container -->
                    <div id="currentImage" class="mt-2 hidden">
                        <img id="currentImagePreview" src="" alt="Current image" class="max-w-32 h-auto rounded">
                        <button type="button" id="removeImage" class="mt-2 text-red-600 hover:text-red-800 text-sm">Remove Image</button>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelTask" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" id="saveTask" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // API configuration and auth setup
    const API_BASE = '/api';
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    // Redirect to login if not authenticated
    if (!token) {
        window.location.href = '/login';
    }

    // Set user welcome message
    document.getElementById('userWelcome').textContent = `${user.name || 'User'}`;

    //API wrapper with auth headers, handles file uploads too
    async function apiCall(endpoint, method = 'GET', data = null, isFormData = false) {
        const config = {
            method,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        };

        if (!isFormData) {
            config.headers['Content-Type'] = 'application/json';
        }

        if (data) {
            config.body = isFormData ? data : JSON.stringify(data);
        }

        const response = await fetch(API_BASE + endpoint, config);
        
        //Handle token expiration
        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            return;
        }

        const result = await response.json();
        return { response, result };
    }

    // DOM Elements
    const tasksTable = document.getElementById('tasksTable');
    const loading = document.getElementById('loading');
    const errorMessage = document.getElementById('errorMessage');
    const taskModal = document.getElementById('taskModal');
    const taskForm = document.getElementById('taskForm');
    const modalTitle = document.getElementById('modalTitle');
    const pageInfo = document.getElementById('pageInfo');
    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');
    const pageNumbers = document.getElementById('pageNumbers');
    const perPageSelect = document.getElementById('perPage');
    const customPerPageInput = document.getElementById('customPerPage');

    // Global state management
    let currentPage = 1;
    let totalPages = 1;
    let isEditing = false;
    let removeImageFlag = false; // tracks if user wants to remove current image
    let currentView = 'active'; // 'active' or 'trash'

    // Pagination preferences management
    function savePaginationPrefs() {
        const prefs = {
            perPageSelect: perPageSelect.value,
            customValue: customPerPageInput.value
        };
        localStorage.setItem('task_pagination_prefs', JSON.stringify(prefs));
    }

    function loadPaginationPrefs() {
        try {
            const saved = localStorage.getItem('task_pagination_prefs');
            if (saved) {
                const prefs = JSON.parse(saved);
                
                // Restore per page selection
                if (prefs.perPageSelect) {
                    perPageSelect.value = prefs.perPageSelect;
                }
                
                // Restore custom value and show input if needed
                if (prefs.customValue) {
                    customPerPageInput.value = prefs.customValue;
                }
                
                // Show custom input if custom is selected
                if (perPageSelect.value === 'custom') {
                    customPerPageInput.classList.remove('hidden');
                }
            }
        } catch (e) {
            // If there's an error, just use defaults
            console.warn('Could not load pagination preferences:', e);
        }
    }

    // Utility functions
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
    }

    function hideError() {
        errorMessage.classList.add('hidden');
    }

    // Modal-specific error functions
    function showModalError(message) {
        const modalErrorMessage = document.getElementById('modalErrorMessage');
        modalErrorMessage.textContent = message;
        modalErrorMessage.classList.remove('hidden');
    }

    function hideModalError() {
        const modalErrorMessage = document.getElementById('modalErrorMessage');
        modalErrorMessage.classList.add('hidden');
    }

    function showSuccess(message) {
        // Quick success feedback
        const existing = document.getElementById('tempSuccess');
        if (existing) existing.remove();
        
        const successDiv = document.createElement('div');
        successDiv.id = 'tempSuccess';
        successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
        successDiv.textContent = message;
        document.body.appendChild(successDiv);
        
        setTimeout(() => successDiv.remove(), 3000);
    }

    function showLoading() {
        loading.classList.remove('hidden');
        tasksTable.innerHTML = '';
    }

    function hideLoading() {
        loading.classList.add('hidden');
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }

    function getStatusBadge(status) {
        const colors = {
            'to-do': 'bg-gray-100 text-gray-800',
            'in-progress': 'bg-yellow-100 text-yellow-800',
            'done': 'bg-green-100 text-green-800'
        };
        
        return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colors[status] || colors['to-do']}">${status}</span>`;
    }

    function getProgressBar(progress) {
        if (!progress || progress.total === 0) {
            return '<span class="text-gray-500">No subtasks</span>';
        }
        
        // Visual progress bar with completion stats
        return `
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: ${progress.percentage}%"></div>
            </div>
            <span class="text-xs text-gray-600">${progress.completed}/${progress.total} (${progress.percentage}%)</span>
        `;
    }

    // Load tasks with debounced search
    let searchTimeout;
    async function loadTasks(skipLoading = false) {
        if (!skipLoading) showLoading();
        hideError();

        let perPage = perPageSelect.value === 'custom' ? 
            (parseInt(customPerPageInput.value) || 10) : 
            (parseInt(perPageSelect.value) || 10);
        
        // Ensure perPage is within bounds
        perPage = Math.min(Math.max(perPage, 1), 100);
        
        const params = new URLSearchParams({
            page: currentPage,
            per_page: perPage
        });

        // Build query params from form inputs
        const search = document.getElementById('search').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const sortBy = document.getElementById('sortBy').value;
        const sortOrder = document.getElementById('sortOrder').value;

        if (search) params.append('search', search);
        if (statusFilter) params.append('status_filter', statusFilter);
        if (sortBy) params.append('sort_by', sortBy);
        if (sortOrder) params.append('sort_order', sortOrder);

        try {
            // Choose endpoint based on current view
            const endpoint = currentView === 'trash' ? `/tasks/trashed?${params}` : `/tasks?${params}`;
            const { response, result } = await apiCall(endpoint);

            if (response.ok && result.success) {
                displayTasks(result.data.data || result.data);
                updatePagination(result.data);
            } else {
                showError(result.message || 'Failed to load tasks');
            }
        } catch (error) {
            showError('Network error. Please try again.');
        } finally {
            hideLoading();
        }
    }

    // Switch between active and trash views
    function switchView(view) {
        currentView = view;
        currentPage = 1;
        
        // Update tab styling
        const activeTab = document.getElementById('activeTasksTab');
        const trashTab = document.getElementById('trashedTasksTab');
        const addTaskSection = document.getElementById('addTaskSection');
        
        if (view === 'active') {
            activeTab.className = 'py-2 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600';
            trashTab.className = 'py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300';
            addTaskSection.classList.remove('hidden');
        } else {
            activeTab.className = 'py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300';
            trashTab.className = 'py-2 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600';
            addTaskSection.classList.add('hidden');
        }
        
        loadTasks();
    }

    // Display tasks in table
    function displayTasks(tasks) {
        if (tasks.length === 0) {
            const message = currentView === 'trash' ? 'No trashed tasks found' : 'No tasks found';
            tasksTable.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">${message}</td></tr>`;
            return;
        }

        tasksTable.innerHTML = tasks.map(task => {
            const actionsHtml = currentView === 'trash' ? 
                // Trash view actions
                `<div class="flex space-x-2">
                    <button onclick="restoreTask(${task.id})" class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs hover:bg-green-200 transition-colors">
                        Restore
                    </button>
                    <button onclick="permanentDeleteTask(${task.id})" class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs hover:bg-red-200 transition-colors">
                        Delete Forever
                    </button>
                </div>` :
                // Active view actions
                `<div class="flex space-x-2">
                    <button onclick="editTask(${task.id})" class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs hover:bg-blue-200 transition-colors">
                        Edit
                    </button>
                    <button onclick="deleteTask(${task.id})" class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs hover:bg-red-200 transition-colors">
                        Delete
                    </button>
                </div>`;

            const dateToShow = currentView === 'trash' ? 
                `<div>
                    <div class="text-xs text-gray-500">Created: ${formatDate(task.created_at)}</div>
                    <div class="text-xs text-red-500">Deleted: ${formatDate(task.deleted_at)}</div>
                </div>` :
                formatDate(task.created_at);

            // Always show expand icon for active tasks to allow adding subtasks
            const expandIcon = currentView === 'active' ? 
                `<button onclick="toggleSubtasks(${task.id})" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg id="expand-icon-${task.id}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>` :
                '<div class="w-4 h-4"></div>';

            return `
                <tr class="${currentView === 'trash' ? 'bg-red-50' : ''}" id="task-row-${task.id}">
                    <td class="px-6 py-4 text-center">
                        ${expandIcon}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="${task.title}">${task.title}</div>
                        <div class="text-sm text-gray-500 truncate max-w-xs" title="${task.content || ''}">${task.content || ''}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${getStatusBadge(task.status)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${currentView === 'trash' ? '<span class="text-gray-400 text-xs">-</span>' : getProgressBar(task.progress)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${dateToShow}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${task.task_image ? 
                            `<div class="w-8 h-8 bg-gray-200 rounded cursor-pointer flex items-center justify-center" 
                                  onclick="showImageModal('${task.task_image.split('/').pop()}')"
                                  data-filename="${task.task_image.split('/').pop()}"
                                  title="Click to view image">
                                <span class="text-xs text-gray-500">IMG</span>
                             </div>` : 
                            '<span class="text-gray-400 text-xs">No image</span>'
                        }
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        ${actionsHtml}
                    </td>
                </tr>
                <tr id="subtasks-row-${task.id}" class="hidden">
                    <td colspan="7" class="px-6 py-2 bg-gray-50">
                        <div id="subtasks-container-${task.id}">
                            <div class="text-center text-gray-500 py-4">
                                <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-gray-400 mr-2"></div>
                                Loading subtasks...
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Update pagination
    function updatePagination(data) {
        currentPage = data.current_page;
        totalPages = data.last_page;
        
        pageInfo.textContent = `${data.from || 0} to ${data.to || 0} of ${data.total} results`;
        
        prevPage.disabled = currentPage <= 1;
        nextPage.disabled = currentPage >= totalPages;
        
        // Generate page numbers
        generatePageNumbers();
    }

    // Generate numbered pagination buttons
    function generatePageNumbers() {
        pageNumbers.innerHTML = '';
        
        if (totalPages <= 1) return;
        
        // Calculate which pages to show
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        // Adjust if we're near the beginning or end
        if (currentPage <= 3) {
            endPage = Math.min(5, totalPages);
        }
        if (currentPage >= totalPages - 2) {
            startPage = Math.max(1, totalPages - 4);
        }
        
        // Add first page and ellipsis if needed
        if (startPage > 1) {
            addPageButton(1);
            if (startPage > 2) {
                addEllipsis();
            }
        }
        
        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            addPageButton(i);
        }
        
        // Add ellipsis and last page if needed
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                addEllipsis();
            }
            addPageButton(totalPages);
        }
    }

    function addPageButton(pageNum) {
        const button = document.createElement('button');
        button.textContent = pageNum;
        button.className = currentPage === pageNum 
            ? 'px-3 py-2 bg-blue-600 text-white border border-blue-600 rounded-md text-sm'
            : 'px-3 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 text-sm';
        
        button.addEventListener('click', () => {
            if (currentPage !== pageNum) {
                currentPage = pageNum;
                loadTasks();
            }
        });
        
        pageNumbers.appendChild(button);
    }

    function addEllipsis() {
        const ellipsis = document.createElement('span');
        ellipsis.textContent = '...';
        ellipsis.className = 'px-3 py-2 text-gray-500 text-sm';
        pageNumbers.appendChild(ellipsis);
    }

    // Event Listeners
    document.getElementById('logoutBtn').addEventListener('click', async () => {
        try {
            await apiCall('/auth/logout', 'POST');
        } catch (error) {
            // Continue with logout even if API fails
        }
        
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
    });

    // Real-time search with debounce
    document.getElementById('search').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadTasks();
        }, 300);
    });

    document.getElementById('searchBtn').addEventListener('click', () => {
        currentPage = 1;
        loadTasks();
    });

    document.getElementById('clearBtn').addEventListener('click', () => {
        document.getElementById('search').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('sortBy').value = 'created_at';
        document.getElementById('sortOrder').value = 'desc';
        currentPage = 1;
        loadTasks();
    });

    document.getElementById('addTaskBtn').addEventListener('click', () => {
        openTaskModal('create', 'task');
    });

    function openTaskModal(mode, type, taskId = null, parentTaskId = null) {
        isEditing = mode === 'edit';
        removeImageFlag = false;
        
        // Reset form and hidden fields
        taskForm.reset();
        document.getElementById('taskId').value = taskId || '';
        document.getElementById('parentTaskId').value = parentTaskId || '';
        document.getElementById('isSubtask').value = type === 'subtask' ? 'true' : 'false';
        document.getElementById('currentImage').classList.add('hidden');
        
        // Hide any previous modal errors
        hideModalError();
        
        // Set modal title and button text
        if (type === 'subtask') {
            modalTitle.textContent = mode === 'edit' ? 'Edit Subtask' : 'Add New Subtask';
            document.getElementById('saveTask').textContent = mode === 'edit' ? 'Save Subtask' : 'Create Subtask';
            // Hide task state field for subtasks
            document.getElementById('taskStateContainer').style.display = 'none';
        } else {
            modalTitle.textContent = mode === 'edit' ? 'Edit Task' : 'Add New Task';
            document.getElementById('saveTask').textContent = mode === 'edit' ? 'Save Task' : 'Create Task';
            document.getElementById('taskStateContainer').style.display = 'block';
        }
        
        taskModal.classList.remove('hidden');
    }

    document.getElementById('cancelTask').addEventListener('click', () => {
        taskModal.classList.add('hidden');
    });

    document.getElementById('removeImage').addEventListener('click', () => {
        removeImageFlag = true;
        document.getElementById('currentImage').classList.add('hidden');
    });

    prevPage.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadTasks();
        }
    });

    nextPage.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadTasks();
        }
    });

    // Items per page change event
    perPageSelect.addEventListener('change', () => {
        if (perPageSelect.value === 'custom') {
            customPerPageInput.classList.remove('hidden');
            customPerPageInput.focus();
        } else {
            customPerPageInput.classList.add('hidden');
            currentPage = 1; // Reset to first page when changing items per page
            loadTasks();
        }
        // Save preference
        savePaginationPrefs();
    });

    // Custom per page input events
    customPerPageInput.addEventListener('input', () => {
        const value = parseInt(customPerPageInput.value);
        if (value && value >= 1 && value <= 100) {
            currentPage = 1;
            loadTasks();
            savePaginationPrefs(); // Save on valid input
        }
    });

    customPerPageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const value = parseInt(customPerPageInput.value);
            if (value && value >= 1 && value <= 100) {
                currentPage = 1;
                loadTasks();
                savePaginationPrefs(); // Save on enter
            }
        }
    });

    customPerPageInput.addEventListener('blur', () => {
        const value = parseInt(customPerPageInput.value);
        if (!value || value < 1 || value > 100) {
            customPerPageInput.value = 10; // Default to 10 if invalid
        }
        currentPage = 1;
        loadTasks();
        savePaginationPrefs(); // Save on blur
    });

    // Task/Subtask form with loading states
    taskForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const saveBtn = document.getElementById('saveTask');
        const originalText = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
        
        const formData = new FormData(taskForm);
        const taskId = document.getElementById('taskId').value;
        const parentTaskId = document.getElementById('parentTaskId').value;
        const isSubtaskForm = document.getElementById('isSubtask').value === 'true';
        
        // Handle explicit image removal case
        if (removeImageFlag && !formData.get('task_image').name) {
            formData.set('task_image', '');
        }

        try {
            let endpoint, method;
            
            if (isSubtaskForm) {
                // Subtask operations
                if (isEditing && taskId) {
                    endpoint = `/tasks/${parentTaskId}/subtasks/${taskId}`;
                    method = 'POST'; // Laravel method spoofing for file uploads
                    formData.append('_method', 'PUT');
                } else {
                    endpoint = `/tasks/${parentTaskId}/subtasks`;
                    method = 'POST';
                }
            } else {
                // Task operations
                if (isEditing && taskId) {
                    endpoint = `/tasks/${taskId}`;
                    method = 'POST'; // Laravel method spoofing for file uploads
                    formData.append('_method', 'PUT');
                } else {
                    endpoint = '/tasks';
                    method = 'POST';
                }
            }

            const { response, result } = await apiCall(endpoint, method, formData, true);

            if (response.ok && result.success) {
                taskModal.classList.add('hidden');
                
                if (isSubtaskForm) {
                    // Reset cache and refresh subtasks and parent task
                    const subtasksContainer = document.getElementById(`subtasks-container-${parentTaskId}`);
                    subtasksContainer.dataset.loaded = 'false';
                    await loadSubtasks(parentTaskId);
                    loadTasks(true);
                    showSuccess(isEditing ? 'Subtask updated successfully' : 'Subtask created successfully');
                } else {
                    loadTasks();
                    showSuccess(isEditing ? 'Task updated successfully' : 'Task created successfully');
                }
            } else {
                showModalError(result.message || `Failed to save ${isSubtaskForm ? 'subtask' : 'task'}`);
            }
        } catch (error) {
            showModalError('Network error. Please try again.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    });

    // Global functions for table actions
    window.editTask = async (id) => {
        try {
            const { response, result } = await apiCall(`/tasks/${id}`);
            
            if (response.ok && result.success) {
                const task = result.data;
                
                openTaskModal('edit', 'task', task.id);
                
                // Populate form fields
                document.getElementById('taskTitle').value = task.title;
                document.getElementById('taskContent').value = task.content || '';
                document.getElementById('taskStatus').value = task.status;
                document.getElementById('taskState').value = task.task_state;
                
                // Handle current image with auth headers
                if (task.task_image) {
                    const imagePreview = document.getElementById('currentImagePreview');
                    const filename = task.task_image.split('/').pop();
                    
                    // Load image with auth token
                    fetch(`/api/images/${filename}`, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    })
                    .then(response => response.blob())
                    .then(blob => {
                        imagePreview.src = URL.createObjectURL(blob);
                        document.getElementById('currentImage').classList.remove('hidden');
                    })
                    .catch(() => {
                        document.getElementById('currentImage').classList.add('hidden');
                    });
                } else {
                    document.getElementById('currentImage').classList.add('hidden');
                }
            }
        } catch (error) {
            showError('Failed to load task details');
        }
    };

    window.deleteTask = async (id) => {
        // Confirmation dialog
        const confirmDelete = confirm('Are you sure you want to delete this task?\n\nThis will move it to trash.');
        if (confirmDelete) {
            try {
                const { response, result } = await apiCall(`/tasks/${id}`, 'DELETE');
                
                if (response.ok && result.success) {
                    showSuccess('Task moved to trash successfully');
                    loadTasks();
                } else {
                    showError(result.message || 'Failed to delete task');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        }
    };

    window.restoreTask = async (id) => {
        const confirmRestore = confirm('Are you sure you want to restore this task?');
        if (confirmRestore) {
            try {
                const { response, result } = await apiCall(`/tasks/${id}/restore`, 'PATCH');
                
                if (response.ok && result.success) {
                    showSuccess('Task restored successfully');
                    loadTasks();
                } else {
                    showError(result.message || 'Failed to restore task');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        }
    };

    window.permanentDeleteTask = async (id) => {
        const confirmDelete = confirm('Are you sure you want to permanently delete this task?\n\nThis action cannot be undone!');
        if (confirmDelete) {
            try {
                const { response, result } = await apiCall(`/tasks/${id}/force-delete`, 'DELETE');
                
                if (response.ok && result.success) {
                    showSuccess('Task permanently deleted');
                    loadTasks();
                } else {
                    showError(result.message || 'Failed to permanently delete task');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        }
    };

    // Toggle subtasks visibility
    window.toggleSubtasks = async (taskId) => {
        const subtasksRow = document.getElementById(`subtasks-row-${taskId}`);
        const expandIcon = document.getElementById(`expand-icon-${taskId}`);
        const subtasksContainer = document.getElementById(`subtasks-container-${taskId}`);

        if (subtasksRow.classList.contains('hidden')) {
            // Show subtasks
            subtasksRow.classList.remove('hidden');
            expandIcon.style.transform = 'rotate(90deg)';
            
            // Load subtasks if not already loaded
            if (!subtasksContainer.dataset.loaded) {
                await loadSubtasks(taskId);
                subtasksContainer.dataset.loaded = 'true';
            }
        } else {
            // Hide subtasks
            subtasksRow.classList.add('hidden');
            expandIcon.style.transform = 'rotate(0deg)';
        }
    };

    // Load subtasks for a specific task
    async function loadSubtasks(taskId) {
        const subtasksContainer = document.getElementById(`subtasks-container-${taskId}`);
        
        try {
            // Choose endpoint based on current view
            const endpoint = currentView === 'trash' 
                ? `/tasks/${taskId}/subtasks/trashed`
                : `/tasks/${taskId}/subtasks`;
            
            const { response, result } = await apiCall(endpoint);

            if (response.ok && result.success) {
                displaySubtasks(taskId, result.data);
            } else {
                subtasksContainer.innerHTML = `
                    <div class="text-center text-red-500 py-4">
                        Failed to load subtasks: ${result.message || 'Unknown error'}
                    </div>
                `;
            }
        } catch (error) {
            subtasksContainer.innerHTML = `
                <div class="text-center text-red-500 py-4">
                    Network error while loading subtasks. Please try again.
                </div>
            `;
        }
    }

    // Display subtasks within the expanded row
    function displaySubtasks(taskId, subtasks) {
        const subtasksContainer = document.getElementById(`subtasks-container-${taskId}`);
        
        if (subtasks.length === 0) {
            const message = currentView === 'trash' ? 'No trashed subtasks' : 'No subtasks found';
            const addSubtaskButton = currentView === 'active' ? 
                `<div class="mt-3 pt-3 border-t border-gray-200">
                    <button onclick="openSubtaskModal(${taskId})" class="bg-purple-100 text-purple-700 px-3 py-2 rounded text-sm hover:bg-purple-200 transition-colors">
                        + Add Subtask
                    </button>
                </div>` : '';
            
            subtasksContainer.innerHTML = `
                <div class="py-2">
                    <div class="text-center text-gray-500 py-4 italic">
                        ${message}
                    </div>
                    ${addSubtaskButton}
                </div>
            `;
            return;
        }

        const subtasksHtml = subtasks.map(subtask => {
            const subtaskActions = currentView === 'trash' ?
                `<div class="flex space-x-1">
                    <button onclick="restoreSubtask(${taskId}, ${subtask.id})" class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs hover:bg-green-200 transition-colors">
                        Restore
                    </button>
                    <button onclick="permanentDeleteSubtask(${taskId}, ${subtask.id})" class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs hover:bg-red-200 transition-colors">
                        Delete Forever
                    </button>
                </div>` :
                `<div class="flex space-x-1">
                    <button onclick="editSubtask(${taskId}, ${subtask.id})" class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs hover:bg-blue-200 transition-colors">
                        Edit
                    </button>
                    <button onclick="deleteSubtask(${taskId}, ${subtask.id})" class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs hover:bg-red-200 transition-colors">
                        Delete
                    </button>
                </div>`;

            const dateToShow = currentView === 'trash' ?
                `<div class="text-xs">
                    <div class="text-gray-500">Created: ${formatDate(subtask.created_at)}</div>
                    <div class="text-red-500">Deleted: ${formatDate(subtask.deleted_at)}</div>
                </div>` :
                `<div class="text-xs text-gray-500">${formatDate(subtask.created_at)}</div>`;

            return `
                <div class="${currentView === 'trash' ? 'bg-red-25' : 'bg-white'} border border-gray-200 rounded p-3 mb-2">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="inline-block w-2 h-2 bg-blue-400 rounded-full"></span>
                                <div class="text-sm font-medium text-gray-900 truncate" title="${subtask.title}">
                                    ${subtask.title}
                                </div>
                                <div class="flex-shrink-0">
                                    ${getStatusBadge(subtask.status)}
                                </div>
                            </div>
                            ${subtask.content ? `<div class="text-sm text-gray-600 truncate mb-2" title="${subtask.content}">${subtask.content}</div>` : ''}
                            <div class="flex items-center space-x-4">
                                ${dateToShow}
                                ${subtask.task_image ? 
                                    `<div class="flex items-center space-x-1">
                                        <span class="text-xs text-gray-500">IMG</span>
                                        <button onclick="showImageModal('${subtask.task_image.split('/').pop()}')" class="text-xs text-blue-600 hover:underline">
                                            View Image
                                        </button>
                                    </div>` : ''
                                }
                            </div>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            ${subtaskActions}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        const addSubtaskButton = currentView === 'active' ? 
            `<div class="mt-3 pt-3 border-t border-gray-200">
                <button onclick="openSubtaskModal(${taskId})" class="bg-purple-100 text-purple-700 px-3 py-2 rounded text-sm hover:bg-purple-200 transition-colors">
                    + Add Subtask
                </button>
            </div>` : '';

        subtasksContainer.innerHTML = `
            <div class="py-2">
                <div class="text-sm font-medium text-gray-700 mb-3 border-b border-gray-200 pb-2">
                    Subtasks (${subtasks.length})
                </div>
                ${subtasksHtml}
                ${addSubtaskButton}
            </div>
        `;
    }

    // Image modal for table thumbnails
    window.showImageModal = async (filename) => {
        try {
            const response = await fetch(`/api/images/${filename}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const imageUrl = URL.createObjectURL(blob);
                
                // Create modal
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="max-w-4xl max-h-screen p-4">
                        <img src="${imageUrl}" alt="Task image" class="max-w-full max-h-full object-contain rounded">
                        <button class="absolute top-4 right-4 text-white text-2xl">&times;</button>
                    </div>
                `;
                
                modal.addEventListener('click', () => {
                    document.body.removeChild(modal);
                    URL.revokeObjectURL(imageUrl);
                });
                
                document.body.appendChild(modal);
            }
        } catch (error) {
            showError('Failed to load image');
        }
    };

    // Subtask action functions
    window.deleteSubtask = async (taskId, subtaskId) => {
        const confirmDelete = confirm('Are you sure you want to delete this subtask?\n\nThis will move it to trash.');
        if (confirmDelete) {
            try {
                const { response, result } = await apiCall(`/tasks/${taskId}/subtasks/${subtaskId}`, 'DELETE');
                
                if (response.ok && result.success) {
                    showSuccess('Subtask moved to trash successfully');
                    // Reset cache and reload subtasks
                    const subtasksContainer = document.getElementById(`subtasks-container-${taskId}`);
                    subtasksContainer.dataset.loaded = 'false';
                    await loadSubtasks(taskId);
                    loadTasks(true); // Refresh to update progress
                } else {
                    showError(result.message || 'Failed to delete subtask');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        }
    };

    window.restoreSubtask = async (taskId, subtaskId) => {
        const confirmRestore = confirm('Are you sure you want to restore this subtask?');
        if (confirmRestore) {
            try {
                const { response, result } = await apiCall(`/tasks/${taskId}/subtasks/${subtaskId}/restore`, 'PATCH');
                
                if (response.ok && result.success) {
                    showSuccess('Subtask restored successfully');
                    const subtasksContainer = document.getElementById(`subtasks-container-${taskId}`);
                    subtasksContainer.dataset.loaded = 'false';
                    await loadSubtasks(taskId);
                    loadTasks(true);
                } else {
                    showError(result.message || 'Failed to restore subtask');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        }
    };

    window.permanentDeleteSubtask = async (taskId, subtaskId) => {
        const confirmDelete = confirm('Are you sure you want to permanently delete this subtask?\n\nThis action cannot be undone!');
        if (confirmDelete) {
            try {
                const { response, result } = await apiCall(`/tasks/${taskId}/subtasks/${subtaskId}/force-delete`, 'DELETE');
                
                if (response.ok && result.success) {
                    showSuccess('Subtask permanently deleted');
                    const subtasksContainer = document.getElementById(`subtasks-container-${taskId}`);
                    subtasksContainer.dataset.loaded = 'false';
                    await loadSubtasks(taskId);
                    loadTasks(true);
                } else {
                    showError(result.message || 'Failed to permanently delete subtask');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        }
    };

    window.editSubtask = async (taskId, subtaskId) => {
        try {
            const { response, result } = await apiCall(`/tasks/${taskId}/subtasks/${subtaskId}`);
            
            if (response.ok && result.success) {
                const subtask = result.data;
                
                openTaskModal('edit', 'subtask', subtask.id, taskId);
                
                // Populate form fields
                document.getElementById('taskTitle').value = subtask.title;
                document.getElementById('taskContent').value = subtask.content || '';
                document.getElementById('taskStatus').value = subtask.status;
                
                // Handle current image with auth headers
                if (subtask.task_image) {
                    const imagePreview = document.getElementById('currentImagePreview');
                    const filename = subtask.task_image.split('/').pop();
                    
                    // Load image with auth token
                    fetch(`/api/images/${filename}`, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    })
                    .then(response => response.blob())
                    .then(blob => {
                        imagePreview.src = URL.createObjectURL(blob);
                        document.getElementById('currentImage').classList.remove('hidden');
                    })
                    .catch(() => {
                        document.getElementById('currentImage').classList.add('hidden');
                    });
                } else {
                    document.getElementById('currentImage').classList.add('hidden');
                }
            }
        } catch (error) {
            showError('Failed to load subtask details');
        }
    };

    window.openSubtaskModal = async (taskId) => {
        openTaskModal('create', 'subtask', null, taskId);
    };

    // Tab event listeners
    document.getElementById('activeTasksTab').addEventListener('click', () => {
        switchView('active');
    });

    document.getElementById('trashedTasksTab').addEventListener('click', () => {
        switchView('trash');
    });

    // Load initial data on page load
    loadPaginationPrefs(); // Restore saved preferences first
    loadTasks();
    </script>
</body>
</html>