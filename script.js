document.addEventListener('DOMContentLoaded', () => {
  const wrapper = document.querySelector('.wrapper');
  const registerLink = document.querySelector('.register-link');
  const loginLink = document.querySelector('.login-link');

  if (registerLink && loginLink && wrapper) {
    registerLink.onclick = () => {
      wrapper.classList.add('active');
    };

    loginLink.onclick = () => {
      wrapper.classList.remove('active');
    };
  }

  // Normal Login Form Fetch Handling
  const loginForm = document.querySelector('.form-box.login form');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(loginForm);
      const username = formData.get('username').trim();
      const password = formData.get('password').trim();

      fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
      })
        .then(res => res.text())
        .then(data => {
          if (data.includes('Login successful')) {
            // Redirect on success
            window.location.href = 'dashboard.php';
          } else {
            alert('Login failed: ' + data);
          }
        })
        .catch(err => {
          console.error('Login fetch error:', err);
          alert('Error during login. Please try again.');
        });
    });
  }

  // Google Sign-In integration
  if (window.google && google.accounts && google.accounts.id) {
    google.accounts.id.initialize({
      client_id: "420369560421-pphfemjrvsofr546lcgrcash5p9v375a.apps.googleusercontent.com",
      callback: handleCredentialResponse
    });

    document.querySelectorAll('.google-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        google.accounts.id.prompt();
      });
    });
  }

  function handleCredentialResponse(response) {
    const idToken = response.credential;
    console.log("Google ID Token:", idToken);

    fetch('google-login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_token: idToken })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = "dashboard.php";
        } else {
          alert('Google Sign-in failed: ' + data.message);
        }
      })
      .catch(err => {
        console.error('Google login error:', err);
        alert('Something went wrong during Google Sign-in.');
      });
  }
});
