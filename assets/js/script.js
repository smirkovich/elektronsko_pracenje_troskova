// Main JS file

// Validacija polja
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.getElementsByTagName('input');
    let valid = true;

    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].hasAttribute('required') && inputs[i].value === '') {
            alert(inputs[i].name + ' polje je obavezno.');
            valid = false;
            break;
        }
    }
    return valid;
}

// Prikaz ukupnih troškova sa grafikom (koristeći Chart.js)
function renderExpenseChart(labels, data) {
    const ctx = document.getElementById('expensesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ukupni troškovi',
                data: data,
                backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56', '#4bc0c0'],
                borderColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56', '#4bc0c0'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Prikaz ukupnog troška i troška za aktuelni mesec
function calculateTotalExpenses(expenses) {
    let total = 0;
    let currentMonthTotal = 0;
    const currentMonth = new Date().getMonth() + 1; // meseci idu od 0-11

    expenses.forEach(expense => {
        total += parseFloat(expense.amount);
        const expenseDate = new Date(expense.date);
        if (expenseDate.getMonth() + 1 === currentMonth) {
            currentMonthTotal += parseFloat(expense.amount);
        }
    });

    document.getElementById('totalExpenses').innerText = `Ukupni troškovi: ${total} RSD`;
    document.getElementById('currentMonthExpenses').innerText = `Troškovi za ovaj mesec: ${currentMonthTotal} RSD`;
}
