document.addEventListener('DOMContentLoaded', () => {
    const fromSelect = document.getElementById('routeFrom');
    const toSelect = document.getElementById('routeTo');
    const searchForm = document.getElementById('routeSearchForm');
    const searchResultContainer = document.getElementById('routeResultContainer');

    // 1. Fetch available origins and destinations on page load
    fetch('backend/route_search_module.php')
        .then(response => response.json())
        .then(res => {
            if (!res.success) {
                fromSelect.innerHTML = `<option disabled>${res.error}</option>`;
                return;
            }

            // Populate Origin "From" Dropdown
            fromSelect.innerHTML = '<option value="" disabled selected>Select Origin...</option>';
            res.origins.forEach(place => {
                const option = document.createElement('option');
                option.value = place;
                option.textContent = place;
                fromSelect.appendChild(option);
            });

            // Populate Destination "To" Dropdown
            toSelect.innerHTML = '<option value="" disabled selected>Select Destination...</option>';
            res.destinations.forEach(place => {
                const option = document.createElement('option');
                option.value = place;
                option.textContent = place;
                toSelect.appendChild(option);
            });
        })
        .catch(err => console.error('Error fetching route options:', err));

    // 2. Process form submission to find routes
    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const fromValue = fromSelect.value;
        const toValue = toSelect.value;

        if (!fromValue || !toValue) return;

        searchResultContainer.innerHTML = `
            <div class="alert alert-info">
                <p>Searching for operational schedules and calculating risk metrics...</p>
            </div>
        `;

        fetch('backend/route_search_module.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ from: fromValue, to: toValue })
        })
        .then(response => response.json())
        .then(res => {
            if (!res.success) {
                searchResultContainer.innerHTML = `
                    <div class="alert alert-info" style="border-color: #ffc107;">
                        <p><strong>Notice:</strong> ${res.error}</p>
                    </div>
                `;
                return;
            }

            // Clear loading layout
            searchResultContainer.innerHTML = '';

            // Generate HTML for each scheduled bus found on this route
            res.data.forEach((bus, index) => {
                let badgeClass = 'badge-very-low';
                switch (bus.crowd_risk_level) {
                    case 'Very High': badgeClass = 'badge-very-high'; break;
                    case 'High':      badgeClass = 'badge-high'; break;
                    case 'Moderate':  badgeClass = 'badge-moderate'; break;
                    case 'Medium':    badgeClass = 'badge-medium'; break;
                    case 'Low':       badgeClass = 'badge-low'; break;
                    default:          badgeClass = 'badge-very-low';
                }

                const busCard = document.createElement('div');
                busCard.style.cssText = 'text-align: left; margin-top: 20px; padding: 20px; border: 1px solid var(--light-gray); border-radius: var(--border-radius); background: #fff;';
                
                busCard.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h4 style="margin: 0; font-size: 18px; color: var(--primary-dark);">
                            Bus #${index + 1}: ${bus.service_class} (${bus.departure_time})
                        </h4>
                        <span class="risk-badge ${badgeClass}">${bus.crowd_risk_level}</span>
                    </div>
                    <p style="font-size: 13px; color: var(--text-light); margin-bottom: 12px;"><strong>Route Path:</strong> Via ${bus.via}</p>

                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">District</span>
                            <span class="stat-value">${bus.district}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Population</span>
                            <span class="stat-value">${bus.population} Million</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Time Slot</span>
                            <span class="stat-value">${bus.time_slot}</span>
                        </div>
                    </div>

                    <div style="background-color: var(--light-gray); padding: 10px; border-radius: var(--border-radius); text-align: center; margin-top: 15px;">
                        <span style="font-size: 11px; font-weight: 600; color: var(--text-light); text-transform: uppercase; display: block;">Crowd Risk Score</span>
                        <span style="font-size: 24px; font-weight: 800; color: var(--primary-dark);">${bus.crowd_risk_score}</span>
                    </div>
                `;
                searchResultContainer.appendChild(busCard);
            });
        })
        .catch(err => {
            console.error('Error conducting route search:', err);
            searchResultContainer.innerHTML = `
                <div class="alert alert-info" style="border-color: #dc3545; background-color: rgba(220,53,69,0.1);">
                    <p style="color: #dc3545;"><strong>Error:</strong> Critical connection breakdown with search module backend.</p>
                </div>
            `;
        });
    });
});