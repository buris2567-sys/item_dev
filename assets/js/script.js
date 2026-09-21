document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchInput");
    const tableRows = document.querySelectorAll("#itemTable tbody tr");

    // ฟังก์ชันค้นหาข้อมูลในตาราง
    searchInput.addEventListener("keyup", function(e) {
        const term = e.target.value.toLowerCase();
        
        tableRows.forEach(function(row) {
            // ค้นหาจากคอลัมน์ชื่อรายการ (คอลัมน์ที่ 3)
            const itemName = row.querySelector(".item-name").textContent.toLowerCase();
            
            if (itemName.includes(term)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});