document.addEventListener('DOMContentLoaded', function() {
    function fetchDashboardData() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../php/adminDashboardData.php');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            var data = null;
            try { data = JSON.parse(xhr.responseText); } catch(e) {}
            if (data && data.success) {
                document.getElementById('stat-transactions').textContent = data.data.totalTransactions;
                document.getElementById('stat-sales').textContent = '₹' + data.data.totalSalesVolume.toFixed(2);
                document.getElementById('stat-commission').textContent = '₹' + data.data.totalCommissionEarned.toFixed(2);
                document.getElementById('stat-rate').textContent = data.data.currentCommissionRate + '%';
                
                var tbody = document.querySelector('#transactions-table tbody');
                tbody.innerHTML = '';
                if (data.data.recentTransactions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No recent transactions</td></tr>';
                } else {
                    data.data.recentTransactions.forEach(function(tx) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + tx.id + '</td>' +
                                       '<td>' + escapeHtml(tx.seller_name) + '</td>' +
                                       '<td>' + escapeHtml(tx.buyer_name) + '</td>' +
                                       '<td>' + escapeHtml(tx.book_title) + '</td>' +
                                       '<td>₹' + parseFloat(tx.sale_price).toFixed(2) + '</td>' +
                                       '<td>₹' + parseFloat(tx.commission_amount).toFixed(2) + '</td>' +
                                       '<td>' + tx.transaction_date + '</td>';
                        tbody.appendChild(tr);
                    });
                }
            } else if (data && !data.success && data.message === 'Unauthorized') {
                window.location.href = 'adminLogin.php';
            }
        };
        xhr.send();
    }

    function escapeHtml(str) {
        return (str + '').replace(/[&<>"']/g, function(c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];
        });
    }

    var form = document.getElementById('rate-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var rate = document.getElementById('rate-input').value;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../php/updateCommissionRate.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                var data = null;
                try { data = JSON.parse(xhr.responseText); } catch(e) {}
                if (data && data.success) {
                    var msg = document.getElementById('rate-message');
                    msg.style.display = 'inline';
                    setTimeout(function() { msg.style.display = 'none'; }, 2000);
                    fetchDashboardData();
                } else {
                    alert('Update failed: ' + (data ? data.message : 'Unknown error'));
                }
            };
            xhr.send('rate=' + encodeURIComponent(rate));
        });
    }

    // Initial fetch
    fetchDashboardData();
    
    // Auto-refresh every 10 seconds
    setInterval(fetchDashboardData, 10000);
});
