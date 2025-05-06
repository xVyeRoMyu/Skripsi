// midtrans-handler.js
function loadMidtransScript(clientKey) {
  return new Promise((resolve) => {
    if (window.snap) return resolve();
    
    const script = document.createElement('script');
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', clientKey);
    script.onload = resolve;
    document.head.appendChild(script);
  });
}

async function processPayment(bookings, totalAmount, customerDetails) {
  try {
    await loadMidtransScript('SB-Mid-client-1vPjTg-CVEbmOtZT');
    
    const transactionDetails = {
      transaction_details: {
        gross_amount: totalAmount,
        order_id: `ORDER-${Date.now()}-${Math.floor(Math.random() * 1000)}`
      },
      item_details: bookings.map(booking => ({
        id: booking.type,
        price: parsePrice(booking.price),
        quantity: 1,
        name: booking.title
      })),
      customer_details: customerDetails
    };

    const response = await fetch('/create-transaction', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(transactionDetails)
    });

    return await response.json();
  } catch (error) {
    console.error('Payment processing error:', error);
    throw error;
  }
}

// Helper function to parse price strings
function parsePrice(priceString) {
  return parseFloat(priceString.replace(/[^0-9]/g, '')) || 0;
}