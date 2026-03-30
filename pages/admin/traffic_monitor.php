<?php
/**
 * Page : Traffic Monitor (Monitoring en temps réel)
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Protéger - Vérifier accès admin
if (!isset($_SESSION['admin_access']) || !$_SESSION['admin_access']) {
    redirect(BASE_URL . '/?page=admin');
}

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <h2>Traffic Monitor - Monitoring en Temps Réel</h2>
    
    <div id="monitor" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Les données seront remplies par JavaScript -->
    </div>

    <!-- Graphique -->
    <div style="background: white; padding: 20px; border-radius: 10px;">
        <h3>Activité en Direct</h3>
        <canvas id="trafficChart" style="max-height: 400px;"></canvas>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Récupérer les stats
async function updateStats() {
    const response = await fetch('<?php echo BASE_URL; ?>/?page=traffic-api');
    const data = await response.json();
    
    // Mettre à jour les cartes
    const monitor = document.getElementById('monitor');
    monitor.innerHTML = `
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666;">Visiteurs Actuels</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #e74c3c;">${data.current_visitors || 0}</p>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666;">Visites Aujourd'hui</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #3498db;">${data.today_visits || 0}</p>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666;">Nouvelles Inscriptions</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #27ae60;">${data.new_registrations || 0}</p>
        </div>
    `;
}

// Mettre à jour les stats toutes les 5 secondes
setInterval(updateStats, 5000);
updateStats(); // Appel initial
</script>

<?php require_once APP_PATH . '/views/footer.php'; ?>
