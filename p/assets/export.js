function downloadCSV(csv, filename) {
    const blob = new Blob(["\uFEFF" + csv], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = filename || "budget_data.csv"; // Fallback filename
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function exportTableToCSV() {
    const table = document.getElementById("dataTable");
    if (!table) {
        console.error("Table not found!");
        return;
    }

    const rows = table.querySelectorAll("tr");
    const csv = [];
    const headers = [];
    const headerCells = rows[0].querySelectorAll("th");

    // Filter out "Actions" and "Recurring" from headers
    headerCells.forEach((cell, index) => {
        const text = cell.innerText.trim();
        if (text !== "Actions" && text !== "Recurring") {
            headers.push(text);
        }
    });
    csv.push(headers.join(","));

    // Process data rows (skip unwanted columns)
    for (let i = 1; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll("td");
        
        cols.forEach((col, index) => {
            const headerText = headerCells[index]?.innerText.trim();
            if (headerText !== "Actions" && headerText !== "Recurring") {
                let cellText = col.innerText.trim();
                // Fix ₹ symbol if needed
                if (headerText === "Amount") {
                    cellText = cellText.replace(/â‚¹/g, "₹");
                }
                row.push(cellText);
            }
        });
        
        csv.push(row.join(","));
    }

    // Trigger download with proper filename
    downloadCSV(csv.join("\n"), "budget_data.csv");
}

