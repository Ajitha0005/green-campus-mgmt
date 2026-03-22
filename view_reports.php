<?php
require_once 'config.php';
require_once 'includes/header.php';

// Fetch all records
$energyStmt = $pdo->query("SELECT e.*, u.name as added_by FROM energy_usage e JOIN users u ON e.created_by = u.id ORDER BY created_at DESC");
$energyData = $energyStmt->fetchAll();

$wasteStmt = $pdo->query("SELECT w.*, u.name as added_by FROM waste w JOIN users u ON w.created_by = u.id ORDER BY date DESC");
$wasteData = $wasteStmt->fetchAll();

$plantStmt = $pdo->query("SELECT p.*, u.name as added_by FROM plantation p JOIN users u ON p.created_by = u.id ORDER BY date_planted DESC");
$plantData = $plantStmt->fetchAll();
?>

<div class="page-title">Comprehensive Reports</div>

<div class="table-card" style="margin-bottom: 2rem;">
    <div class="table-header">
        <h3 style="color: var(--dark-green);">Energy Logs</h3>
        <input type="text" class="search-bar" placeholder="Search energy..." onkeyup="filterTable(this, 'energyTable')">
    </div>
    <div class="table-responsive">
        <table id="energyTable">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Electricity (kWh)</th>
                    <th>Water (L)</th>
                    <th>Added By</th>
                    <th>Date Logged</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($energyData as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['month']); ?></td>
                    <td><?php echo htmlspecialchars($row['electricity_units']); ?></td>
                    <td><?php echo htmlspecialchars($row['water_usage']); ?></td>
                    <td><?php echo htmlspecialchars($row['added_by']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($energyData)): ?>
                <tr><td colspan="5" style="text-align: center;">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="table-card" style="margin-bottom: 2rem;">
    <div class="table-header">
        <h3 style="color: var(--dark-green);">Waste Management</h3>
        <input type="text" class="search-bar" placeholder="Search waste..." onkeyup="filterTable(this, 'wasteTable')">
    </div>
    <div class="table-responsive">
        <table id="wasteTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Dry Waste (kg)</th>
                    <th>Wet Waste (kg)</th>
                    <th>Added By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($wasteData as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['dry_waste']); ?></td>
                    <td><?php echo htmlspecialchars($row['wet_waste']); ?></td>
                    <td><?php echo htmlspecialchars($row['added_by']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($wasteData)): ?>
                <tr><td colspan="4" style="text-align: center;">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <h3 style="color: var(--dark-green);">Plantation Records</h3>
        <input type="text" class="search-bar" placeholder="Search plantations..." onkeyup="filterTable(this, 'plantTable')">
    </div>
    <div class="table-responsive">
        <table id="plantTable">
            <thead>
                <tr>
                    <th>Tree Name</th>
                    <th>Location</th>
                    <th>Date Planted</th>
                    <th>Added By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($plantData as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['tree_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td><?php echo htmlspecialchars($row['date_planted']); ?></td>
                    <td><?php echo htmlspecialchars($row['added_by']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($plantData)): ?>
                <tr><td colspan="4" style="text-align: center;">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable(input, tableId) {
    let filter = input.value.toLowerCase();
    let rows = document.getElementById(tableId).getElementsByTagName("tbody")[0].getElementsByTagName("tr");
    
    for (let i = 0; i < rows.length; i++) {
        let text = rows[i].textContent.toLowerCase();
        rows[i].style.display = text.includes(filter) ? "" : "none";
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
