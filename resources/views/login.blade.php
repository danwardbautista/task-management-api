<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - Login</title>
    <!-- CDN fast dev -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Task Management Login</h1>
        
        <!-- Main login form, submits to API -->
        <form id="loginForm" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter your email"
                >
                <div id="email-error" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter your password"
                >
                <div id="password-error" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>
            
            <button 
                type="submit" 
                id="loginBtn"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
            >
                Login
            </button>
        </form>
        
        <!-- Message containers for API responses -->
        <div id="errorMessage" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded hidden">
        </div>
        
        <div id="successMessage" class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded hidden">
        </div>
        
        <!-- Register Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Don't have an account? 
                <a href="#" id="showRegister" class="text-blue-600 hover:text-blue-800">Register here</a>
            </p>
        </div>
    </div>

    <!-- Registration modal overlay, decide later if want separate page -->
    <div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-6">Register</h2>
            
            <form id="registerForm" class="space-y-4">
                <div>
                    <label for="regName" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input 
                        type="text" 
                        id="regName" 
                        name="name" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter your name"
                    >
                    <div id="regName-error" class="text-red-600 text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label for="regEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        id="regEmail" 
                        name="email" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter your email"
                    >
                    <div id="regEmail-error" class="text-red-600 text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label for="regPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input 
                        type="password" 
                        id="regPassword" 
                        name="password" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter your password"
                    >
                    <div id="regPassword-error" class="text-red-600 text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label for="regPasswordConfirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input 
                        type="password" 
                        id="regPasswordConfirm" 
                        name="password_confirmation" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Confirm your password"
                    >
                    <div id="regPasswordConfirm-error" class="text-red-600 text-sm mt-1 hidden"></div>
                </div>
                
                <button 
                    type="submit" 
                    id="registerBtn"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
                >
                    Register
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <button id="closeRegister" class="text-gray-600 hover:text-gray-800">Cancel</button>
            </div>
        </div>
    </div>

    <script>
    // API config, using same domain for now
    const API_BASE = '/api';
    
    // DOM Elements
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const errorMessage = document.getElementById('errorMessage');
    const successMessage = document.getElementById('successMessage');
    const registerModal = document.getElementById('registerModal');
    const showRegister = document.getElementById('showRegister');
    const closeRegister = document.getElementById('closeRegister');
    const loginBtn = document.getElementById('loginBtn');
    const registerBtn = document.getElementById('registerBtn');

    // Message display helpers 
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
        successMessage.classList.add('hidden');
    }

    function showSuccess(message) {
        successMessage.textContent = message;
        successMessage.classList.remove('hidden');
        errorMessage.classList.add('hidden');
    }

    function hideMessages() {
        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');
    }

    // Field-level error handling
    function showFieldError(fieldId, message) {
        const errorElement = document.getElementById(`${fieldId}-error`);
        const inputElement = document.getElementById(fieldId);
        
        if (errorElement && inputElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            inputElement.classList.add('border-red-500', 'focus:ring-red-500');
            inputElement.classList.remove('border-gray-300', 'focus:ring-blue-500');
        }
    }

    function hideFieldError(fieldId) {
        const errorElement = document.getElementById(`${fieldId}-error`);
        const inputElement = document.getElementById(fieldId);
        
        if (errorElement && inputElement) {
            errorElement.classList.add('hidden');
            inputElement.classList.remove('border-red-500', 'focus:ring-red-500');
            inputElement.classList.add('border-gray-300', 'focus:ring-blue-500');
        }
    }

    function clearAllFieldErrors(formType = 'login') {
        const fields = formType === 'login' 
            ? ['email', 'password']
            : ['regName', 'regEmail', 'regPassword', 'regPasswordConfirm'];
        
        fields.forEach(field => hideFieldError(field));
    }

    function displayValidationErrors(errors, formType = 'login') {
        // Clear existing field errors first
        clearAllFieldErrors(formType);
        
        if (formType === 'login') {
            if (errors.email) showFieldError('email', errors.email[0]);
            if (errors.password) showFieldError('password', errors.password[0]);
        } else {
            if (errors.name) showFieldError('regName', errors.name[0]);
            if (errors.email) showFieldError('regEmail', errors.email[0]);
            if (errors.password) showFieldError('regPassword', errors.password[0]);
            if (errors.password_confirmation) showFieldError('regPasswordConfirm', errors.password_confirmation[0]);
        }
    }

    // Generic API wrapper, handles auth headers and errors
    async function apiCall(endpoint, method = 'GET', data = null) {
        const config = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        if (data) {
            config.body = JSON.stringify(data);
        }

        const response = await fetch(API_BASE + endpoint, config);
        const result = await response.json();
        
        return { response, result };
    }

    // Login handler
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideMessages();
        clearAllFieldErrors('login');
        
        const formData = new FormData(loginForm);
        const data = {
            email: formData.get('email'),
            password: formData.get('password')
        };

        loginBtn.disabled = true;
        loginBtn.textContent = 'Logging in...';

        try {
            const { response, result } = await apiCall('/auth/login', 'POST', data);

            if (response.ok && result.success) {
                // Store credentials for subsequent API calls
                localStorage.setItem('auth_token', result.data.token);
                localStorage.setItem('user', JSON.stringify(result.data.user));
                
                showSuccess('Login successful! Redirecting...');
                
                // Small delay for user feedback then redirect
                setTimeout(() => {
                    window.location.href = '/tasks';
                }, 1000);
            } else {
                // Handle validation errors
                if (response.status === 422 && result.errors) {
                    displayValidationErrors(result.errors, 'login');
                } else {
                    showError(result.message || 'Login failed');
                }
            }
        } catch (error) {
            showError('Network error. Please try again.');
        } finally {
            loginBtn.disabled = false;
            loginBtn.textContent = 'Login';
        }
    });

    // Register handler
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideMessages();
        clearAllFieldErrors('register');
        
        const formData = new FormData(registerForm);
        const password = formData.get('password');
        const passwordConfirm = formData.get('password_confirmation');
        
        // Client-side password match check
        if (password !== passwordConfirm) {
            showFieldError('regPasswordConfirm', 'Passwords do not match');
            return;
        }

        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            password: password,
            password_confirmation: passwordConfirm
        };

        registerBtn.disabled = true;
        registerBtn.textContent = 'Registering...';

        try {
            const { response, result } = await apiCall('/auth/register', 'POST', data);

            if (response.ok && result.success) {
                // Store token
                localStorage.setItem('auth_token', result.data.token);
                localStorage.setItem('user', JSON.stringify(result.data.user));
                
                showSuccess('Registration successful! Redirecting...');
                registerModal.classList.add('hidden');
                
                // Redirect to tasks page
                setTimeout(() => {
                    window.location.href = '/tasks';
                }, 1000);
            } else {
                // Handle validation errors
                if (response.status === 422 && result.errors) {
                    displayValidationErrors(result.errors, 'register');
                } else {
                    showError(result.message || 'Registration failed');
                }
            }
        } catch (error) {
            showError('Network error. Please try again.');
        } finally {
            registerBtn.disabled = false;
            registerBtn.textContent = 'Register';
        }
    });

    // Clear field errors on input
    function addInputClearErrorListeners() {
        // Login form fields
        document.getElementById('email').addEventListener('input', () => hideFieldError('email'));
        document.getElementById('password').addEventListener('input', () => hideFieldError('password'));
        
        // Register form fields
        document.getElementById('regName').addEventListener('input', () => hideFieldError('regName'));
        document.getElementById('regEmail').addEventListener('input', () => hideFieldError('regEmail'));
        document.getElementById('regPassword').addEventListener('input', () => hideFieldError('regPassword'));
        document.getElementById('regPasswordConfirm').addEventListener('input', () => hideFieldError('regPasswordConfirm'));
    }

    // Modal handlers
    showRegister.addEventListener('click', (e) => {
        e.preventDefault();
        registerModal.classList.remove('hidden');
        hideMessages();
        clearAllFieldErrors('register');
    });

    closeRegister.addEventListener('click', () => {
        registerModal.classList.add('hidden');
        registerForm.reset();
        clearAllFieldErrors('register');
    });

    // Initialize input listeners
    addInputClearErrorListeners();

    // Skip login if already authenticated 
    if (localStorage.getItem('auth_token')) {
        window.location.href = '/tasks';
    }
    </script>
</body>
</html>