document.addEventListener('DOMContentLoaded', () => {
    const recFromSelect = document.getElementById('recFrom');
    const recToSelect = document.getElementById('recTo');
    const recForm = document.getElementById('recommendationForm');
    const recResultContainer = document.getElementById('recommendationResultContainer');

    // Populate dropdown selection menus on load
    fetch('backend/time_slot_recommendation.php')
        .then(response => response.json())
        .then(res => {
            if (!res.success) {
                recFromSelect.innerHTML = `<option disabled>${res.error}</option>`;
                return;
            }

            recFromSelect.innerHTML = '<option value="" disabled selected>Select Origin...</option>';
            res.origins.forEach(place => {
                const option = document.createElement('option');
                option.value = place;
                option.textContent = place;
                recFromSelect.appendChild(option);
            });

            recToSelect.innerHTML = '<option value="" disabled selected>Select Destination...</option>';
            res.destinations.forEach(place => {
                const option = document.createElement('option');
                option.value = place;
                option.textContent = place;
                recToSelect.appendChild(option);
            });
        })
        .catch(err => console.error('Initialization error on recommendations:', err));

    // Form submit listener
    recForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const fromVal = recFromSelect.value;
        const toVal = recToSelect.value;

        if (!fromVal || !toVal) return;

        recResultContainer.innerHTML = `
            <div class="alert alert-info">
                <p>Aligning sequential time-slot comparison matrix...</p>
            </div>
        `;

        fetch('backend/time_slot_recommendation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ from: fromVal, to: toVal })
        })
        .then(response => response.json())
        .then(res => {
            if (!res.success) {
                recResultContainer.innerHTML = `
                    <div class="alert alert-info" style="border-color: #ffc107;">
                        <p><strong>Notice:</strong> ${res.error}</p>
                    </div>
                `;
                return;
            }

            const getBadgeClass = (level) => {
                switch (level) {
                    case 'Very High': return 'badge-very-high';
                    case 'High':      return 'badge-high';
                    case 'Moderate':  return 'badge-moderate';
                    case 'Medium':    return 'badge-medium';
                    case 'Low':       return 'badge-low';
                    case 'Very Low':  return 'badge-very-low';
                    default:          return 'badge-info';
                }
            };

            let pairsHTML = '';

            // Generate clean side-by-side container cards
            res.comparisons.forEach((pair) => {
                const orig = pair.original;
                const next = pair.next_available;

                const nextScoreText = next.crowd_risk_score === 'N/A' ? 'N/A' : next.crowd_risk_score;
                const nextBadge = next.crowd_risk_level === 'No Scheduled Bus' 
                    ? `<span class="risk-badge" style="background-color: #6c757d; color: #fff; font-size:11px;">No Service</span>`
                    : `<span class="risk-badge ${getBadgeClass(next.crowd_risk_level)}" style="font-size:11px;">${next.crowd_risk_level}</span>`;

                pairsHTML += `
                    <div style="background: #ffffff; border: 1px solid var(--light-gray); border-radius: var(--border-radius); padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            
                            <div style="padding-right: 15px; border-right: 1px solid var(--light-gray);">
                                <span style="font-size: 11px; font-weight: 700; color: var(--text-light); text-transform: uppercase; display: block; margin-bottom: 4px;">Original Time Slot</span>
                                <span style="font-size: 16px; font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 6px;">${orig.time_slot}</span>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                                    <span style="font-size: 13px; color: var(--text-dark);">Risk Score: <strong>${orig.crowd_risk_score}</strong></span>
                                    <span class="risk-badge ${getBadgeClass(orig.crowd_risk_level)}" style="font-size:11px;">${orig.crowd_risk_level}</span>
                                </div>
                            </div>

                            <div style="padding-left: 5px;">
                                <span style="font-size: 11px; font-weight: 700; color: #007bff; text-transform: uppercase; display: block; margin-bottom: 4px;">Next Available Time Slot</span>
                                <span style="font-size: 16px; font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 6px;">${next.time_slot}</span>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                                    <span style="font-size: 13px; color: var(--text-dark);">Risk Score: <strong>${nextScoreText}</strong></span>
                                    ${nextBadge}
                                </div>
                            </div>

                        </div>
                    </div>
                `;
            });

            recResultContainer.innerHTML = `
                <div style="text-align: left; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--light-gray);">
                    <h4 style="margin-bottom: 15px; color: var(--primary-color); font-size: 20px;">Chronological Route Comparison Matrix</h4>
                    
                    <div class="stats-grid" style="margin-bottom: 20px;">
                        <div class="stat-item">
                            <span class="stat-label">District Context</span>
                            <span class="stat-value" style="color: var(--text-dark);">${res.district}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Target Population</span>
                            <span class="stat-value" style="color: var(--text-dark);">${res.population} Million</span>
                        </div>
                    </div>

                    <div>
                        ${pairsHTML}
                    </div>
                </div>
            `;
        })
        .catch(err => {
            console.error('Processing breakdown:', err);
            recResultContainer.innerHTML = `
                <div class="alert alert-info" style="border-color: #dc3545; background-color: rgba(220,53,69,0.1);">
                    <p style="color: #dc3545;"><strong>Error:</strong> Failed to fetch chronological comparisons.</p>
                </div>
            `;
        });
    });
});