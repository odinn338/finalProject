/* Login Page JavaScript */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');
    const btnLogin = document.querySelector('.btn-login');
    
    // Form Submit
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const remember = document.getElementById('remember').checked;
        
        // Hide error
        errorMessage.classList.remove('show');
        
        // Loading state
        btnLogin.classList.add('loading');
        btnLogin.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Demo credentials: ahmed@debtmate.com / password
            if (email === 'ahmed@debtmate.com' && password === 'password') {
                // Success
                localStorage.setItem('isLoggedIn', 'true');
                localStorage.setItem('userEmail', email);
                window.location.href = 'index.html';
            } else {
                // Error
                showError('البريد الإلكتروني أو كلمة المرور غير صحيحة');
                btnLogin.classList.remove('loading');
                btnLogin.disabled = false;
            }
        }, 1500);
        
        /* 
        // Real Laravel API Example:
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password, remember })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                window.location.href = 'index.html';
            } else {
                showError(data.message || 'حدث خطأ أثناء تسجيل الدخول');
            }
        } catch (error) {
            showError('حدث خطأ في الاتصال. حاول مرة أخرى.');
        } finally {
            btnLogin.classList.remove('loading');
            btnLogin.disabled = false;
        }
        */
    });
    
    // Clear error on input
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function() {
            errorMessage.classList.remove('show');
        });
    });
    
    // Show error function
    function showError(message) {
        document.getElementById('errorText').textContent = message;
        errorMessage.classList.add('show');
        
        setTimeout(() => {
            errorMessage.classList.remove('show');
        }, 5000);
    }
});