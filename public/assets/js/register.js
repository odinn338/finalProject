/* Register Page JavaScript */

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    const btnRegister = document.querySelector('.btn-register');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');

    // Password Strength Check
    passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });

    // Form Submit
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get form data
        const formData = {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            password: document.getElementById('password').value,
            confirmPassword: document.getElementById('confirmPassword').value,
            terms: document.getElementById('terms').checked
        };

        // Validation
        if (!validateForm(formData)) {
            return;
        }

        // Hide messages
        successMessage.classList.remove('show');
        errorMessage.classList.remove('show');

        // Loading state
        btnRegister.classList.add('loading');
        btnRegister.disabled = true;

        // Simulate API call
        setTimeout(() => {
            // Success
            successMessage.classList.add('show');

            // Store user data (demo)
            localStorage.setItem('isLoggedIn', 'true');
            localStorage.setItem('userName', `${formData.firstName} ${formData.lastName}`);
            localStorage.setItem('userEmail', formData.email);

            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
        }, 1500);


    });

    // Validate form
    function validateForm(data) {
        // Check password match
        if (data.password !== data.confirmPassword) {
            showError('كلمتا المرور غير متطابقتين');
            return false;
        }

        // Check password length
        if (data.password.length < 8) {
            showError('كلمة المرور يجب أن تكون 8 أحرف على الأقل');
            return false;
        }

        // Check terms
        if (!data.terms) {
            showError('يجب الموافقة على الشروط والأحكام');
            return false;
        }

        // Check email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            showError('البريد الإلكتروني غير صحيح');
            return false;
        }

        return true;
    }

    // Check password strength
function checkPasswordStrength(password) {
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    // لو العناصر مش موجودة في الـ HTML اوقف
    if (!strengthFill || !strengthText) return;

    strengthFill.classList.remove('weak', 'medium', 'strong');

    if (!password) {
        strengthFill.style.width = '0%';
        strengthText.textContent = 'قوة كلمة المرور';
        return;
    }

    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/\d/.test(password)) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    if (strength <= 2) {
        strengthFill.classList.add('weak');
        strengthText.textContent = 'ضعيفة';
    } else if (strength <= 4) {
        strengthFill.classList.add('medium');
        strengthText.textContent = 'متوسطة';
    } else {
        strengthFill.classList.add('strong');
        strengthText.textContent = 'قوية';
    }
}

    // Show error
    function showError(message) {
        document.getElementById('errorText').textContent = message;
        errorMessage.classList.add('show');

        setTimeout(() => {
            errorMessage.classList.remove('show');
        }, 5000);
    }
});
