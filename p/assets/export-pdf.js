
function exportTableToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Transactions Report", 10, 10);

    let rows = [];
    document.querySelectorAll("table tbody tr").forEach(tr => {
        let cols = tr.querySelectorAll("td");
        let row = Array.from(cols).map(td => td.innerText);
        rows.push(row);
    });

    doc.autoTable({
        head: [["Type", "Category", "Amount", "Description", "Date"]],
        body: rows
    });

    doc.save("transactions.pdf");
}
