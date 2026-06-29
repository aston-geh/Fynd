document.querySelector('.login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('femail').value;
    const password = document.getElementById('fpassword').value;
    
    try {
        const response = await fetch('action_page.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.success && !data.needsVerification) {
            // Login successful
            localStorage.setItem('userEmail', email);
            localStorage.setItem('userId', data.userId);
            localStorage.setItem('sessionToken', data.token);
            
            alert('Logged in successfully!');
            showDashboard();
        } else if (data.needsVerification) {
            // Account created or needs verification
            alert(data.message);
            showVerificationMessage(email);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Connection error');
    }
});

function showVerificationMessage(email) {
    document.querySelector('.login-form').style.display = 'none';
    
    const message = document.createElement('div');
    message.className = 'verification-message';
    message.innerHTML = `
        <h3>Verify Your Email</h3>
        <p>A verification link has been sent to <strong>${email}</strong></p>
        <p>Click the link in your email to verify your account and log in.</p>
        <button onclick="location.reload()">Back to Login</button>
    `;
    
    document.querySelector('.login-container').appendChild(message);
}

// Check if user is already logged in
function checkAuth() {
    const userEmail = localStorage.getItem('userEmail');
    if (userEmail) {
        showDashboard();
    }
}

function showDashboard() {
    document.querySelector('.login-container').style.display = 'none';
    document.querySelector('p').style.display = 'block';
}

function logout() {
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userId');
    localStorage.removeItem('sessionToken');
    location.reload();
}

// Run on page load
checkAuth();