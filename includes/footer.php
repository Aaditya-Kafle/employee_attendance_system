</div>
<footer>
	<p>Employee Attendance & Leave Management. All rights reserved</p>
</footer>
  <script>
        // Confirmation for delete actions
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this record? This action cannot be undone.');
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
</body>
</html>	
