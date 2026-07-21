document.addEventListener('DOMContentLoaded', () => {
    const destinationSelect = document.getElementById('destination');
    const slotSelect = document.getElementById('slot');
    const calculatorForm = document.getElementById('calculatorForm');
    const resultContainer = document.getElementById('resultContainer');

    // 1. Fetch backend options configuration dynamically on window initialization
    fetch('backend/get_options.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                destinationSelect.innerHTML = `<option disabled>${data.error}</option>`;
                return;
            }

            destinationSelect.innerHTML = '<option value="" disabled selected>Choose target route...</option>';
            data.destinations.forEach(dest => {
                const option = document.createElement('option');
                option.value = dest;
                option.textContent = dest;
                destinationSelect.appendChild(option);
            });

            slotSelect.innerHTML = '<option value="" disabled selected>Choose timeline...</option>';
            data.slots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = slot;
                slotSelect.appendChild(option);
            });
        })
        .catch(err => {
            console.error('Initialization Error:', err);
        });

    // 2. Form processing submission pipeline
    calculatorForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const selectedDestination = destinationSelect.value;
        const selectedSlot = slotSelect.value;

        if (!selectedDestination || !selectedSlot) return;

        resultContainer.innerHTML = `
            <div class="alert alert-info">
                <p>Analyzing route details and mapping weights...</p>
            </div>
        `;

        // Request assessment criteria from backend calculate.php engine
        fetch('backend/calculate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                destination: selectedDestination,
                slot: selectedSlot
            })
        })
        .then(response => response.json())
        .then(res => {
            if (!res.success) {
                resultContainer.innerHTML = `
                    <div class="alert alert-info" style="border-color: #ffc107;">
                        <p><strong>Notice:</strong> ${res.error || 'No records matched.'}</p>
                    </div>
                `;
                return;
            }

            const data = res.data;
            
            // Map the corresponding background badge variant based on risk classification thresholds
            let labelClass = 'badge-very-low';
            switch (data.crowd_risk_level) {
                case 'Very High': labelClass = 'badge-very-high'; break;
                case 'High':      labelClass = 'badge-high'; break;
                case 'Moderate':  labelClass = 'badge-moderate'; break;
                case 'Medium':    labelClass = 'badge-medium'; break;
                case 'Low':       labelClass = 'badge-low'; break;
                default:          labelClass = 'badge-very-low';
            }

            // Assemble UI dynamically using your predefined CSS layout rules
            resultContainer.innerHTML = `
                <div style="text-align: left; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--light-gray);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h4 style="margin: 0;">Risk Evaluation Summary</h4>
                        <span class="risk-badge ${labelClass}">${data.crowd_risk_level}</span>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Destination Profile Population</span>
                            <span class="stat-value">${data.population}M</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Urbanization Level Status</span>
                            <span class="stat-value">${data.urbanisation_level}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Interstate Operations Crossing</span>
                            <span class="stat-value">${data.interstate_flag}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Assigned Time Slot Weight</span>
                            <span class="stat-value">${data.time_score}</span>
                        </div>
                    </div>

                    <div style="background-color: var(--light-gray); padding: 15px; border-radius: var(--border-radius); text-align: center; margin-top: 15px;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--text-light); text-transform: uppercase; display: block; margin-bottom: 4px;">Total Crowd Risk Index Score</span>
                        <span style="font-size: 32px; font-weight: 800; color: var(--primary-dark); display: block;">${data.crowd_risk_score}</span>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            console.error('Calculation processing failure:', err);
            resultContainer.innerHTML = `
                <div class="alert alert-info" style="border-color: #dc3545; background-color: rgba(220,53,69,0.1);">
                    <p style="color: #dc3545;"><strong>Error:</strong> Failed to communicate with the risk engine endpoint.</p>
                </div>
            `;
        });
    });
});