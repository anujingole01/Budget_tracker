<?php
require_once 'config.php';
require_once 'auth.php';
require_auth();

$user_id = $_SESSION['user_id'];
$conn->query("CALL ProcessRecurringTransactions()");
?>

<?php include 'header.php'; ?>
<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}
$user_id = $_SESSION['user_id'];

// Process recurring transactions on each load
$conn->query("CALL ProcessRecurringTransactions()");

// UPDATE transaction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $recurrence_interval = $is_recurring ? intval($_POST['recurrence_interval']) : null;

    $stmt = $conn->prepare("UPDATE transactions SET type=?, category=?, amount=?, description=?, is_recurring=?, recurrence_interval=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssdsiiii", $type, $category, $amount, $description, $is_recurring, $recurrence_interval, $id, $user_id);
    $stmt->execute();
}

// ADD transaction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $type = $_POST['type'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $recurrence_interval = $is_recurring ? intval($_POST['recurrence_interval']) : null;

    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, category, amount, description, is_recurring, recurrence_interval) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdsii", $user_id, $type, $category, $amount, $description, $is_recurring, $recurrence_interval);
    $stmt->execute();
}

// DELETE transaction
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM transactions WHERE id=" . intval($_GET['delete']) . " AND user_id=$user_id");
}

// SET budget
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_budget'])) {
    $budget = $_POST['budget'];
    $month = date('n');
    $year = date('Y');

    $conn->query("DELETE FROM budgets WHERE user_id=$user_id AND month=$month AND year=$year");
    $stmt = $conn->prepare("INSERT INTO budgets (user_id, category, amount, month, year) VALUES (?, 'Total', ?, ?, ?)");
    $stmt->bind_param("idii", $user_id, $budget, $month, $year);
    $stmt->execute();
}

// Define categories
$categories = ['Food', 'Rent', 'Transport', 'Entertainment', 'Shopping', 'Utilities', 'Healthcare', 'Education', 'Other'];

// Apply month/year filters
$month_filter = isset($_GET['month']) ? intval($_GET['month']) : null;
$year_filter = isset($_GET['year']) ? intval($_GET['year']) : null;

$where_clause = "WHERE user_id=$user_id";
if ($month_filter) {
    $where_clause .= " AND MONTH(created_at) = $month_filter";
}
if ($year_filter) {
    $where_clause .= " AND YEAR(created_at) = $year_filter";
}

// Get transactions
$transactions_query = "SELECT * FROM transactions $where_clause ORDER BY created_at DESC";
$transactions = $conn->query($transactions_query);

// Calculate summary values
$total_income = $conn->query("SELECT SUM(amount) as total FROM transactions $where_clause AND type='income'")->fetch_assoc()['total'] ?? 0;
$total_expense = $conn->query("SELECT SUM(amount) as total FROM transactions $where_clause AND type='expense'")->fetch_assoc()['total'] ?? 0;
$balance = $total_income - $total_expense;

// Get current budget
$budget_query = $conn->query("SELECT amount FROM budgets WHERE user_id=$user_id AND category='Total' AND month=MONTH(CURRENT_DATE()) AND year=YEAR(CURRENT_DATE())");
$current_budget = $budget_query->fetch_assoc()['amount'] ?? 0;

// Get transaction to edit if requested
$edit_transaction = null;
if (isset($_GET['edit'])) {
    $result = $conn->query("SELECT * FROM transactions WHERE id=" . intval($_GET['edit']) . " AND user_id=$user_id");
    if ($result->num_rows > 0) {
        $edit_transaction = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link href="https://fonts.googleapis.com/css2?family=Inconsolata:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="assets/export.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="assets/charts.js" defer></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --terminal-green: #33ff33;
      --terminal-blue: #3399ff;
      --terminal-red: #ff3333;
      --terminal-yellow: #ffff33;
      --terminal-bg: #0a0a0a;
      --terminal-text: #e0e0e0;
    }
    
    body {
      font-family: 'Inconsolata', monospace;
      background-color: var(--terminal-bg);
      color: var(--terminal-text);
      padding: 1rem;
    }
    
    input, select, textarea {
      background-color: rgba(20, 20, 20, 0.8);
      border: 1px solid #333;
      color: var(--terminal-text);
      padding: 0.5rem;
      margin: 0.25rem 0;
    }
    
    button, .btn {
      background-color: var(--terminal-blue);
      color: black;
      border: none;
      padding: 0.5rem 1rem;
      cursor: pointer;
      font-weight: bold;
      margin: 0.25rem 0;
    }
    
    .btn-success { background-color: var(--terminal-green); }
    .btn-danger { background-color: var(--terminal-red); }
    .btn-warning { background-color: var(--terminal-yellow); color: black; }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 1rem 0;
      color: var(--terminal-text);
    }
    
    th, td {
      border: 1px solid #333;
      padding: 0.75rem;
      text-align: left;
    }
    
    th {
      background-color: rgba(30, 30, 30, 0.8);
      color: var(--terminal-green);
    }
    
    tr:nth-child(even) {
      background-color: rgba(25, 25, 25, 0.5);
    }
    
    .chart-container {
      width: 100%;
      height: 300px;
      margin: 1rem 0;
    }
    
    .status-over { color: var(--terminal-red); }
    .status-under { color: var(--terminal-green); }
    
    a { color: var(--terminal-blue); }
    a:hover { color: var(--terminal-green); }
    
    .form-section {
      background-color: rgba(15, 15, 15, 0.8);
      border: 1px solid #333;
      padding: 1rem;
      margin: 1rem 0;
    }
    
    .grid {
      display: grid;
      gap: 1rem;
    }
    
    .grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
    .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
    .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    .grid-cols-6 { grid-template-columns: repeat(6, 1fr); }
    
    @media (max-width: 768px) {
      .grid-cols-2, .grid-cols-3, .grid-cols-4, .grid-cols-6 {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold" style="color: var(--terminal-green);">
      Budget_Terminal
    </h1>
    <a href="logout.php" class="text-red-500 hover:underline">Logout</a>
  </div>

  <!-- Summary Widget -->
  <div class="flex flex-wrap justify-between items-center form-section gap-4 p-3 terminal-widget mb-6">
  <div class="flex-1 min-w-[120px] text-center">
    <h3 class="text-terminal-green font-mono text-sm mb-1">INCOME</h3>
    <p class="text-lg font-bold" style="color: var(--terminal-green);">₹<?= number_format($total_income, 2) ?></p>
  </div>
  
  <div class="flex-1 min-w-[120px] text-center border-x border-terminal-border">
    <h3 class="text-terminal-red font-mono text-sm mb-1">EXPENSES</h3>
    <p class="text-lgfont-bold" style="color: var(--terminal-red);">₹<?= number_format($total_expense, 2) ?></p>
  </div>
  
  <div class="flex-1 min-w-[120px] text-center">
    <h3 class="font-mono text-sm mb-1">BALANCE</h3>
    <p class="text-lg font-bold <?= $balance >= 0 ? 'text-terminal-green' : 'text-terminal-red' ?>">
      ₹<?= number_format($balance, 2) ?>
    </p>
  </div>
</div>

  <form method="GET" class="form-section grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
    <input type="hidden" name="filter" value="1">
    <select name="month" class="p-2">
      <option value="">All Months</option>
      <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?= $m ?>" <?= isset($_GET['month']) && $_GET['month'] == $m ? 'selected' : '' ?>>
          <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
        </option>
      <?php endfor; ?>
    </select>

    <select name="year" class="p-2">
      <option value="">All Years</option>
      <?php
        $years = range(date('Y'), 2020);
        foreach ($years as $y): ?>
        <option value="<?= $y ?>" <?= isset($_GET['year']) && $_GET['year'] == $y ? 'selected' : '' ?>>
          <?= $y ?>
        </option>
      <?php endforeach; ?>
    </select>

    <button type="submit" class="btn btn-primary">Filter</button>
    <button type="button" onclick="exportTableToCSV()" class="btn btn-warning">
      Export CSV
    </button>

    
  </form>  

  <form method="POST" class="form-section grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
    <input name="budget" type="number" step="0.01" class="p-2" placeholder="Set Budget (₹)" value="<?= $current_budget ?>" required>
    <button type="submit" name="set_budget" class="btn btn-primary">Set Budget</button>
    <p class="col-span-2">
      Spent: ₹<?= $total_expense ?> / Budget: ₹<?= $current_budget ?> 
      <span class="<?= ($total_expense > $current_budget) ? 'status-over' : 'status-under' ?>">
        (<?= ($total_expense > $current_budget) ? 'OVER BUDGET' : 'WITHIN BUDGET' ?>)
      </span>
    </p>
  </form>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
    <div class="form-section">
      <div class="chart-container">
        <canvas id="incomeVsExpenseChart"></canvas>
      </div>
    </div>
    <div class="form-section">
      <div class="chart-container">
        <canvas id="categoryBreakdownChart"></canvas>
      </div>
    </div>
    <div class="form-section md:col-span-3">
      <div class="chart-container">
        <canvas id="monthlyTrendChart"></canvas>
      </div>
    </div>
  </div>

  <script type="application/json" id="chartData">
  <?php
  $total_income_chart = $conn->query("SELECT SUM(amount) as total FROM transactions $where_clause AND type='income'")->fetch_assoc()['total'] ?? 0;
  $total_expense_chart = $conn->query("SELECT SUM(amount) as total FROM transactions $where_clause AND type='expense'")->fetch_assoc()['total'] ?? 0;

  $monthly_result = $conn->query("SELECT MONTH(created_at) as month, SUM(amount) as total FROM transactions $where_clause AND type='expense' GROUP BY MONTH(created_at)");
  $months = $totals = [];
  while ($row = $monthly_result->fetch_assoc()) {
    $months[] = date("M", mktime(0, 0, 0, $row['month'], 1));
    $totals[] = $row['total'];
  }

  $category_result = $conn->query("SELECT category, SUM(amount) as total FROM transactions $where_clause AND type='expense' GROUP BY category");
  $cat_labels = $cat_totals = [];
  while ($row = $category_result->fetch_assoc()) {
    $cat_labels[] = $row['category'];
    $cat_totals[] = $row['total'];
  }

  echo json_encode([
    'totals' => ['income' => $total_income_chart, 'expense' => $total_expense_chart],
    'monthlySpending' => ['labels' => $months, 'data' => $totals],
    'categoryBreakdown' => ['labels' => $cat_labels, 'data' => $cat_totals]
  ]);
  ?>
  </script>

  <form method="POST" class="form-section grid grid-cols-1 md:grid-cols-6 gap-2">
    <input type="hidden" name="id" value="<?= $edit_transaction['id'] ?? '' ?>">
    <select name="type" class="p-2" required>
        <option value="">Type</option>
        <option value="income" <?= isset($edit_transaction) && $edit_transaction['type'] === 'income' ? 'selected' : '' ?>>Income</option>
        <option value="expense" <?= isset($edit_transaction) && $edit_transaction['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
    </select>
    <select name="category" class="p-2" required>
        <option value="">Category</option>
        <?php foreach($categories as $cat): ?>
            <option value="<?= $cat ?>" <?= isset($edit_transaction) && $edit_transaction['category'] === $cat ? 'selected' : '' ?>>
                <?= $cat ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input name="amount" type="number" step="0.01" placeholder="Amount" class="p-2" required value="<?= $edit_transaction['amount'] ?? '' ?>">
    <input name="description" placeholder="Description" class="p-2" value="<?= $edit_transaction['description'] ?? '' ?>">
    <div class="flex flex-col">
        <div class="flex items-center">
            <input type="checkbox" name="is_recurring" id="is_recurring" 
                   <?= isset($edit_transaction['is_recurring']) && $edit_transaction['is_recurring'] ? 'checked' : '' ?>>
            <label for="is_recurring" class="ml-2">Recurring</label>
        </div>
        <select name="recurrence_interval" id="recurrence_interval" class="p-2 mt-1" 
                style="<?= !isset($edit_transaction['is_recurring']) || !$edit_transaction['is_recurring'] ? 'display:none;' : '' ?>">
            <option value="7" <?= isset($edit_transaction['recurrence_interval']) && $edit_transaction['recurrence_interval'] == 7 ? 'selected' : '' ?>>Weekly (7 days)</option>
            <option value="30" <?= !isset($edit_transaction['recurrence_interval']) || $edit_transaction['recurrence_interval'] == 30 ? 'selected' : '' ?>>Monthly (30 days)</option>
            <option value="90" <?= isset($edit_transaction['recurrence_interval']) && $edit_transaction['recurrence_interval'] == 90 ? 'selected' : '' ?>>Quarterly (90 days)</option>
            <option value="365" <?= isset($edit_transaction['recurrence_interval']) && $edit_transaction['recurrence_interval'] == 365 ? 'selected' : '' ?>>Yearly (365 days)</option>
        </select>
    </div>
    <div class="flex gap-2 items-center">
      <button type="submit" name="<?= isset($edit_transaction) ? 'update' : 'add' ?>" class="btn btn-success flex-1">
        <?= isset($edit_transaction) ? 'Update' : 'Add' ?>
      </button>
      <?php if ($edit_transaction): ?>
        <a href="dashboard.php" class="text-terminal-red">Cancel</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="form-section overflow-auto">
    <table>
      <thead>
        <tr>
          <th>Type</th>
          <th>Category</th>
          <th>Amount</th>
          <th>Description</th>
          <th>Recurring</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $transactions->fetch_assoc()): ?>
        <tr>
          <td class="<?= $row['type'] === 'income' ? 'text-green-500' : 'text-red-500' ?>">
            <?= strtoupper($row['type']) ?>
          </td>
          <td><?= $row['category'] ?></td>
          <td>₹<?= number_format($row['amount'], 2) ?></td>
          <td><?= $row['description'] ?></td>
          <td>
            <?= (isset($row['is_recurring']) && $row['is_recurring']) ? 
                '🔁 Every ' . (isset($row['recurrence_interval']) ? $row['recurrence_interval'] : '30') . ' days' : 
                '❌' ?>
          </td>
          <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
          <td class="whitespace-nowrap">
            <a href="?edit=<?= $row['id'] ?>" class="text-terminal-blue">Edit</a>
            <span class="text-gray-500 mx-1">|</span>
            <a href="?delete=<?= $row['id'] ?>" class="text-terminal-red" onclick="return confirm('Delete this transaction?')">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="toast" class="fixed top-5 right-5 py-2 px-4 rounded hidden" style="background-color: var(--terminal-green); color: black;">Transaction Updated!</div>

<script>
  // Toggle recurrence interval visibility
  document.getElementById('is_recurring').addEventListener('change', function() {
    document.getElementById('recurrence_interval').style.display = 
      this.checked ? 'block' : 'none';
  });

  function showToast() {
    const toast = document.getElementById("toast");
    toast.classList.remove("hidden");
    setTimeout(() => toast.classList.add("hidden"), 3000);
  }

  <?php if (isset($_GET['updated'])): ?>
    window.addEventListener("DOMContentLoaded", () => showToast());
  <?php endif; ?>
</script>

</body>
</html>
<?php include 'footer.php'; ?>