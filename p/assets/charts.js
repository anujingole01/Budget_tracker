
document.addEventListener("DOMContentLoaded", () => {
  const { totals, monthlySpending, categoryBreakdown } = JSON.parse(document.getElementById("chartData").textContent);

  // Pie Chart: Income vs Expenses
  new Chart(document.getElementById("incomeVsExpenseChart"), {
    type: "pie",
    data: {
      labels: ["Income", "Expenses"],
      datasets: [{
        data: [totals.income, totals.expense],
        backgroundColor: ["#34d399", "#f87171"]
      }]
    },
    options: {
      plugins: {
        title: {
          display: true,
          text: "Income vs Expenses"
        }
      }
    }
  });

  // Doughnut Chart: Category Breakdown
  new Chart(document.getElementById("categoryBreakdownChart"), {
    type: "doughnut",
    data: {
      labels: categoryBreakdown.labels,
      datasets: [{
        data: categoryBreakdown.data,
        backgroundColor: ["#60a5fa", "#fcd34d", "#a78bfa", "#fb923c", "#4ade80", "#818cf8"]
      }]
    },
    options: {
      plugins: {
        title: {
          display: true,
          text: "Spending by Category"
        }
      }
    }
  });

  // Monthly Expense Trend: Bar Chart
  new Chart(document.getElementById("monthlyTrendChart"), {
    type: "bar",
    data: {
      labels: monthlySpending.labels,
      datasets: [{
        label: "Monthly Expenses",
        data: monthlySpending.data,
        backgroundColor: "#f87171"
      }]
    },
    options: {
      plugins: {
        title: {
          display: true,
          text: "Monthly Spending Trends"
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
});
