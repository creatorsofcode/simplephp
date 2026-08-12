// Contact form AJAX submission with real-time validation (no native HTML validation)
document.addEventListener('DOMContentLoaded', () => {
  const contactForm = document.getElementById('contact-form');
  if (!contactForm) return;

  const formMessage = document.getElementById('form-message');
  const submitBtn = document.getElementById('submit-btn');
  const nameInput = document.getElementById('form-name');
  const emailInput = document.getElementById('form-email');
  const messageInput = document.getElementById('form-message-text');

  const originalBtnText =
    (submitBtn && submitBtn.dataset && submitBtn.dataset.originalText) ||
    (submitBtn ? submitBtn.textContent : 'Send');

  function validateName(name) {
    if (!name || name.trim() === '') return 'Name is required';
    if (name.trim().length < 2) return 'Name must be at least 2 characters';
    return null;
  }

  function validateEmail(email) {
    if (!email || email.trim() === '') return 'Email is required';
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) return 'Please enter a valid email address';
    return null;
  }

  function validateMessage(message) {
    if (!message || message.trim() === '') return 'Message is required';
    if (message.trim().length < 10) return 'Message must be at least 10 characters long';
    return null;
  }

  function clearFieldError(field) {
    if (!field) return;
    field.classList.remove('error', 'success');
    field.style.borderColor = '';
    field.style.borderWidth = '';
    const fieldName = field.id.replace('form-', '');
    const errorSpan = document.getElementById('error-' + fieldName);
    if (errorSpan) {
      errorSpan.textContent = '';
      errorSpan.style.display = 'none';
    }
  }

  function showFieldError(fieldName, message) {
    const field = document.getElementById('form-' + fieldName);
    const errorSpan = document.getElementById('error-' + fieldName);

    if (field) {
      field.classList.add('error');
      field.classList.remove('success');
      field.style.borderColor = '#f44336';
      field.style.borderWidth = '2px';
    }

    if (errorSpan) {
      errorSpan.textContent = message;
      errorSpan.style.display = 'block';
      errorSpan.style.color = '#f44336';
    }
  }

  function showFieldSuccess(fieldName) {
    const field = document.getElementById('form-' + fieldName);
    if (field) {
      field.classList.remove('error');
      field.classList.add('success');
      field.style.borderColor = '#4caf50';
      field.style.borderWidth = '2px';
    }
  }

  function clearAllFieldErrors() {
    [nameInput, emailInput, messageInput].forEach((input) => clearFieldError(input));
  }

  function showSuccess(message) {
    if (formMessage) {
      formMessage.className = 'success';
      formMessage.textContent = message;
      formMessage.style.display = 'block';
    }
    clearAllFieldErrors();
    contactForm.reset();

    setTimeout(() => {
      if (formMessage) formMessage.style.display = 'none';
    }, 5000);
  }

  function showError(message) {
    if (formMessage) {
      formMessage.className = 'error';
      formMessage.textContent = message;
      formMessage.style.display = 'block';
    }
  }

  function attachRealtimeValidation(input, validator, fieldName) {
    if (!input) return;
    input.addEventListener('blur', () => {
      const error = validator(input.value);
      if (error) showFieldError(fieldName, error);
      else {
        clearFieldError(input);
        showFieldSuccess(fieldName);
      }
    });

    input.addEventListener('input', () => {
      if (input.classList.contains('error')) {
        const error = validator(input.value);
        if (!error) {
          clearFieldError(input);
          showFieldSuccess(fieldName);
        }
      }
    });
  }

  attachRealtimeValidation(nameInput, validateName, 'name');
  attachRealtimeValidation(emailInput, validateEmail, 'email');
  attachRealtimeValidation(messageInput, validateMessage, 'message');

  contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    clearAllFieldErrors();
    if (formMessage) {
      formMessage.style.display = 'none';
      formMessage.className = '';
      formMessage.textContent = '';
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending...';
      submitBtn.style.opacity = '0.7';
    }

    const formData = new FormData(contactForm);
    const name = (formData.get('name') || '').toString();
    const email = (formData.get('email') || '').toString();
    const message = (formData.get('message') || '').toString();

    let hasErrors = false;
    const nameError = validateName(name);
    if (nameError) { showFieldError('name', nameError); hasErrors = true; }

    const emailError = validateEmail(email);
    if (emailError) { showFieldError('email', emailError); hasErrors = true; }

    const messageError = validateMessage(message);
    if (messageError) { showFieldError('message', messageError); hasErrors = true; }

    if (hasErrors) {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
        submitBtn.style.opacity = '1';
      }
      const firstError = document.querySelector('.form-field input.error, .form-field textarea.error');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
      }
      return;
    }

    try {
      const response = await fetch('send-email.php', { method: 'POST', body: formData });
      const data = await response.json().catch(() => ({}));

      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
        submitBtn.style.opacity = '1';
      }

      if (response.ok && data && data.success) {
        showSuccess(data.message || 'Thank you! Your message has been sent successfully.');
        if (formMessage) formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      if (data && data.errors) {
        Object.keys(data.errors).forEach((fieldName) => {
          if (fieldName === 'general') showError(data.errors[fieldName]);
          else showFieldError(fieldName, data.errors[fieldName]);
        });
        const firstError = document.querySelector('.form-field input.error, .form-field textarea.error');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstError.focus();
        }
      } else {
        showError('An error occurred. Please try again.');
      }
    } catch (err) {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
        submitBtn.style.opacity = '1';
      }
      showError('Network error. Please check your connection and try again.');
      // eslint-disable-next-line no-console
      console.error(err);
    }
  });
});

