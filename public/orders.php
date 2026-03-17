<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders Dashboard</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        .order {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .timeline {
            display: flex;
            gap: 10px;
        }

        .step {
            flex: 1;
            padding: 10px;
            text-align: center;
            border-radius: 6px;
            background: #ddd;
            font-size: 12px;
        }

        .active {
            background: #4caf50;
            color: white;
            font-weight: bold;
        }

        .pending {
            background: #ccc;
        }
    </style>
</head>
<body>

<h1>📦 Orders Timeline</h1>

<div id="orders"></div>

<script>
function getSteps(status) {
    const steps = ['received', 'processing', 'shipped', 'delivered'];

    const currentIndex = steps.indexOf(status);

    return steps.map((step, index) => {
        let className = 'step pending';

        if (index <= currentIndex) {
            className = 'step active';
        }

        return `<div class="${className}">${step}</div>`;
    }).join('');
}

async function loadOrders() {
    try {
        const res = await fetch('/api/orders.php');
        const data = await res.json();

        const container = document.getElementById('orders');

        if (data.length === 0) {
            container.innerHTML = '<p>No orders found</p>';
            return;
        }

        container.innerHTML = data.map(order => `
            <div class="order">
                <div class="header">
                    <strong>Order #${order.order_id}</strong>
                    <span>${order.total}</span>
                </div>

                <div class="timeline">
                    ${getSteps(order.status)}
                </div>

                <small>Created at: ${order.created_at}</small>
            </div>
        `).join('');

    } catch (err) {
        document.getElementById('orders').innerHTML = 'Error loading';
    }
}

loadOrders();
setInterval(loadOrders, 3000);
</script>

</body>
</html>