<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KSRTC Transit Analytics Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        /* Dashboard 3-Column Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            align-items: start;
            margin-top: 30px;
            margin-bottom: 40px;
        }

        /* Modern Column Card Panels */
        .dashboard-column {
            background: var(--white, #ffffff);
            border: 1px solid var(--light-gray, #e0e0e0);
            border-radius: var(--border-radius, 8px);
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.015);
            display: flex;
            flex-direction: column;
            min-height: 450px;
        }

        .dashboard-column h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color, #009e60);
            margin-top: 0;
            margin-bottom: 8px;
            border-bottom: 2px solid rgba(0, 158, 96, 0.1);
            padding-bottom: 10px;
        }

        .dashboard-column p.column-desc {
            font-size: 13px;
            color: var(--text-light, #666666);
            margin-bottom: 20px;
            line-height: 1.5;
            min-height: 40px;
        }

        /* Form Controls Styling */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .custom-select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            background-color: var(--white);
            color: var(--text-dark);
            transition: var(--transition);
        }
        
        .custom-select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 158, 96, 0.2);
        }

        /* Responsive Layout Overrides */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Conditional Color Matrix Badges */
        .risk-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        .badge-very-high { background-color: #dc3545; color: white; }
        .badge-high { background-color: #ffc107; color: #212529; }
        .badge-moderate { background-color: #0dcaf0; color: #212529; }
        .badge-medium { background-color: #0d6efd; color: white; }
        .badge-low { background-color: #198754; color: white; }
        .badge-very-low { background-color: #6c757d; color: white; }
    </style>
</head>
<body>

    <a href="https://ksrtc.karnataka.gov.in/storage/pdf-files/Time%20Table/Mangaluru.pdf" class="skip-link">Click here to access the timetable</a>

    <header class="navbar">
        <div class="navbar-links">
            <h1>&nbsp;&nbsp;&nbsp;KSRTC Transit Analytics & Recommendations</h1>
        </div>
        <div id="google_translate_element"></div>
    </header>

    <main class="main-content" id="main-content">
        <div class="container" style="max-width: 1400px; width: 95%;">
            <h1 style="text-align: center; margin-bottom: 5px;">Analytics Control Center</h1>
            <p style="text-align: center; color: var(--text-light); margin-bottom: 30px;">Real-time infrastructure capability profiling and rolling chronological optimization.</p>
            
            <div style="background: rgba(0, 158, 96, 0.04); border: 1px solid rgba(0, 158, 96, 0.15); border-radius: var(--border-radius, 8px); padding: 20px 24px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; text-align: left;">
                <div style="flex: 1; min-width: 300px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--primary-color, #009e60); margin: 0;">Integration & Core Data Hub</h3>
                    <p style="font-size: 13px; color: var(--text-dark, #333333); margin: 6px 0 0 0;">Access secondary analytical endpoints: launch the cloud business intelligence matrix for geographical visualizations, or stream the processed master dataset (542 structural route profiles) for local lookup.</p>
                </div>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="https://public.tableau.com/app/profile/leander.chris.dsouza.mca.dsbda.yiascm.2024/viz/Crowd_Estimation_TimeSlot_Recommendation_KSRTC/CrowdEstimationandTime-SlotRecommendationforMangaloreKSRTCBusServices?publish=yes" target="_blank" rel="noopener noreferrer" class="btn-start" style="width: auto; margin: 0; padding: 11px 22px; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; background: #e8762b; border-color: #e8762b; color: #ffffff; font-weight: 600;">
                        Open Tableau Dashboard ↗
                    </a>
                    <a href="download.php" class="btn-start" style="width: auto; margin: 0; padding: 11px 22px; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-weight: 600;">
                        Download Master CSV
                    </a>
                </div>
            </div>

            <div class="dashboard-grid">
                
                <div class="dashboard-column">
                    <h2>Crowd Risk Calculator</h2>
                    <p class="column-desc">Select your destination route and travel window to fetch live infrastructure capacity risk estimates.</p>
                    
                    <form id="calculatorForm">
                        <div class="form-group">
                            <label for="destination">Destination Route</label>
                            <select id="destination" class="custom-select" required>
                                <option value="" disabled selected>Loading destinations...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="slot">Travel Time Window</label>
                            <select id="slot" class="custom-select" required>
                                <option value="" disabled selected>Loading schedules...</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-start" style="width: 100%;">Calculate Risk Level</button>
                    </form>

                    <div id="resultContainer" style="margin-top: 20px;">
                        <div class="alert alert-info" style="font-size: 13px; padding: 12px;">
                            <p style="margin: 0;">Provide parameters to calculate risk factors.</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-column">
                    <h2>Route Search Module</h2>
                    <p class="column-desc">Select your departure city and destination to retrieve running timetables and structural profiles.</p>
                    
                    <form id="routeSearchForm">
                        <div class="form-group">
                            <label for="routeFrom">From (Origin)</label>
                            <select id="routeFrom" class="custom-select" required>
                                <option value="" disabled selected>Loading locations...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="routeTo">To (Destination)</label>
                            <select id="routeTo" class="custom-select" required>
                                <option value="" disabled selected>Loading locations...</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-start" style="width: 100%;">Search Active Routes</button>
                    </form>

                    <div id="routeResultContainer" style="margin-top: 20px;">
                        <div class="alert alert-info" style="font-size: 13px; padding: 12px;">
                            <p style="margin: 0;">Select origin & destination to filter schedules.</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-column">
                    <h2>Time-Slot Recommendation</h2>
                    <p class="column-desc">Find the safest times to travel. Compare off-peak alternatives automatically sorted by risk levels matching your specific route.</p>

                    <form id="recommendationForm">
                        <div class="form-group">
                            <label for="recFrom">From (Origin)</label>
                            <select id="recFrom" class="custom-select" required>
                                <option value="" disabled selected>Loading locations...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="recTo">To (Destination)</label>
                            <select id="recTo" class="custom-select" required>
                                <option value="" disabled selected>Loading locations...</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-start" style="width: 100%;">Get Recommended Time Slots</button>
                    </form>

                    <div id="recommendationResultContainer" style="margin-top: 25px;">
                        <div class="alert alert-info" style="font-size: 13px; padding: 12px;">
                            <p style="margin: 0;">Select your origin and destination to view optimal transit windows.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="frontend/js/route_search.js"></script>
    <script src="frontend/js/time_slot_recommendation.js"></script>
    <script src="frontend/js/main.js"></script>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> KSRTC Transit Analytics Portal</p>
        <small>Formulated using live localized census profiling models.</small>
    </footer>
</body>
</html>