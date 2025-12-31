// assets/js/login.js
function handleLogin(formId, messageContainerId) {
    const form = document.getElementById(formId);
    const messageContainer = document.getElementById(messageContainerId);
    const submitBtn = form.querySelector('button[type="submit"]');
    const loading = document.getElementById('loading');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable button and show loading
        submitBtn.disabled = true;
        if (loading) loading.classList.remove('hidden');
        
        // Clear messages
        messageContainer.innerHTML = '';
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('../backend/auth.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Show success message
                messageContainer.innerHTML = `
                    <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        ${data.message} - Mengalihkan...
                    </div>
                `;
                
                // Redirect
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
                
            } else {
                // Show error
                messageContainer.innerHTML = `
                    <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        ${data.message}
                    </div>
                `;
                
                // Re-enable button
                submitBtn.disabled = false;
                if (loading) loading.classList.add('hidden');
            }
            
        } catch (error) {
            console.error('Login error:', error);
            
            messageContainer.innerHTML = `
                <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Terjadi kesalahan. Silakan coba lagi.
                </div>
            `;
            
            submitBtn.disabled = false;
            if (loading) loading.classList.add('hidden');
        }
    });
}