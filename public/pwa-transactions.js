// PWA offline transaction logic
const PWA_TX_KEY = 'offline_transactions';

function saveTransactionOffline(data) {
  let txs = JSON.parse(localStorage.getItem(PWA_TX_KEY) || '[]');
  txs.push(data);
  localStorage.setItem(PWA_TX_KEY, JSON.stringify(txs));
}

function syncTransactions() {
  let txs = JSON.parse(localStorage.getItem(PWA_TX_KEY) || '[]');
  if (!txs.length) return;
  txs.forEach((tx, idx) => {
    fetch('/transactions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
      },
      body: JSON.stringify(tx)
    })
    .then(res => {
      if (res.ok) {
        txs.splice(idx, 1);
        localStorage.setItem(PWA_TX_KEY, JSON.stringify(txs));
      }
    });
  });
}

window.addEventListener('online', syncTransactions);

// Expose for form usage
window.saveTransactionOffline = saveTransactionOffline;
window.syncTransactions = syncTransactions;
