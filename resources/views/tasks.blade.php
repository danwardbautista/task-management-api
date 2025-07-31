<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
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
        <div class="mb-6">
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
        <div id="pagination" class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-700">
                Showing <span id="pageInfo"></span>
            </div>
            <div class="flex space-x-2">
                <button id="prevPage" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                    Previous
                </button>
                <button id="nextPage" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                    Next
                </button>
            </div>
        </div>
    </main>

    <!-- Task create/edit modal overlay -->
    <div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl max-h-screen overflow-y-auto">
            <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add New Task</h2>
            
            <form id="taskForm" class="space-y-4">
                <input type="hidden" id="taskId">
                
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
                
                <div>
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

    // Global state management
    let currentPage = 1;
    let totalPages = 1;
    let isEditing = false;
    let removeImageFlag = false; // tracks if user wants to remove current image

    // Utility functions
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
    }

    function hideError() {
        errorMessage.classList.add('hidden');
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

        const params = new URLSearchParams({
            page: currentPage,
            per_page: 10
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
            const { response, result } = await apiCall(`/tasks?${params}`);

            if (response.ok && result.success) {
                displayTasks(result.data.data);
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

    // Display tasks in table
    function displayTasks(tasks) {
        if (tasks.length === 0) {
            tasksTable.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No tasks found</td></tr>';
            return;
        }

        tasksTable.innerHTML = tasks.map(task => `
            <tr>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="${task.title}">${task.title}</div>
                    <div class="text-sm text-gray-500 truncate max-w-xs" title="${task.content || ''}">${task.content || ''}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${getStatusBadge(task.status)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${getProgressBar(task.progress)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${formatDate(task.created_at)}
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
                    <div class="flex space-x-2">
                        <button onclick="editTask(${task.id})" class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs hover:bg-blue-200 transition-colors">
                            Edit
                        </button>
                        <button onclick="deleteTask(${task.id})" class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs hover:bg-red-200 transition-colors">
                            Delete
                        </button>
                        <button onclick="viewSubtasks(${task.id})" class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs hover:bg-green-200 transition-colors">
                            Subtasks
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Update pagination
    function updatePagination(data) {
        currentPage = data.current_page;
        totalPages = data.last_page;
        
        pageInfo.textContent = `${data.from || 0} to ${data.to || 0} of ${data.total} results`;
        
        prevPage.disabled = currentPage <= 1;
        nextPage.disabled = currentPage >= totalPages;
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
        isEditing = false;
        removeImageFlag = false;
        modalTitle.textContent = 'Add New Task';
        taskForm.reset();
        document.getElementById('taskId').value = '';
        document.getElementById('currentImage').classList.add('hidden');
        taskModal.classList.remove('hidden');
    });

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

    // Task form with loading states
    taskForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const saveBtn = document.getElementById('saveTask');
        const originalText = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
        
        const formData = new FormData(taskForm);
        const taskId = document.getElementById('taskId').value;
        
        // Handle explicit image removal case
        if (removeImageFlag && !formData.get('task_image').name) {
            formData.set('task_image', '');
        }

        try {
            let endpoint, method;
            
            if (isEditing && taskId) {
                endpoint = `/tasks/${taskId}`;
                method = 'POST'; // Laravel method spoofing for file uploads
                formData.append('_method', 'PUT');
            } else {
                endpoint = '/tasks';
                method = 'POST';
            }

            const { response, result } = await apiCall(endpoint, method, formData, true);

            if (response.ok && result.success) {
                taskModal.classList.add('hidden');
                loadTasks();
                // Success feedback
                showSuccess('Task saved successfully');
                setTimeout(() => hideError(), 3000);
            } else {
                showError(result.message || 'Failed to save task');
            }
        } catch (error) {
            showError('Network error. Please try again.');
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
                
                isEditing = true;
                removeImageFlag = false;
                modalTitle.textContent = 'Edit Task';
                
                document.getElementById('taskId').value = task.id;
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
                
                taskModal.classList.remove('hidden');
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
                    loadTasks();
                } else {
                    showError(result.message || 'Failed to delete task');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        }
    };

    window.viewSubtasks = (id) => {
        // TODO: implement subtasks view
        alert(`Sub task`);
    };

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

    // Load initial data on page load
    loadTasks();
    </script>
</body>
</html>