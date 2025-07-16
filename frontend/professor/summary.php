<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: /attendance-system/index.php");
    exit();
}

// Sample dummy stats; replace with DB queries to get real data
$totalClasses = 15;
$totalStudents = 120;
$totalAttendance = 1100;
$totalLate = 75;
$totalAbsent = 45;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Attendance Summary - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
</head>
<body class="dashboard-bg">
  <?php include "../../includes/header.php"; ?>
  <div class="layout">
    <?php include "../../includes/sidebar.php"; ?>
    <main class="main-content">
      <h2>Attendance Summary</h2>
      <div class="summary-grid prof-summary-row" id="summary-stats"></div>
      <div class="summary-chart-container">
        <h3>Attendance Breakdown</h3>
        <canvas id="attendancePieChart" width="350" height="350" style="max-width:100%;"></canvas>
      </div>
    </main>
  </div>
  <?php include "../../includes/footer.php"; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <script>
  // Global variable to store chart instance
  let attendanceChart = null;
  
  // Function to destroy existing chart
  function destroyChart() {
    if (attendanceChart) {
      try {
        attendanceChart.destroy();
      } catch (e) {
        console.log('Chart already destroyed or not initialized');
      }
      attendanceChart = null;
    }
  }
  
  // Function to destroy any existing charts on the canvas
  function destroyExistingCharts(canvas) {
    console.log('Attempting to destroy existing charts on canvas...');
    
    // Method 1: Try using Chart.js's getChart method (Chart.js 4.x)
    try {
      if (typeof Chart.getChart === 'function') {
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
          console.log('Found existing chart using getChart, destroying it...');
          existingChart.destroy();
          return new Promise(resolve => setTimeout(resolve, 100));
        }
      }
    } catch (e) {
      console.log('getChart method failed:', e);
    }
    
    // Method 2: Try using Chart.js registry (older versions)
    try {
      if (Chart.registry && Chart.registry.controllers) {
        const existingCharts = Chart.registry.controllers;
        for (let chartId in existingCharts) {
          const chart = existingCharts[chartId];
          if (chart && chart.canvas === canvas) {
            console.log('Found existing chart with ID:', chartId, 'destroying it...');
            try {
              chart.destroy();
            } catch (e) {
              console.log('Error destroying existing chart:', e);
            }
          }
        }
      }
    } catch (e) {
      console.log('Registry method failed:', e);
    }
    
    // Method 3: Try destroying our tracked chart instance
    destroyChart();
    
    // Method 4: Force canvas cleanup by clearing it
    try {
      const context = canvas.getContext('2d');
      context.clearRect(0, 0, canvas.width, canvas.height);
      console.log('Canvas cleared');
    } catch (e) {
      console.log('Canvas clear failed:', e);
    }
    
    // Method 5: Try to remove any existing chart instances from the canvas
    try {
      if (canvas.chart) {
        console.log('Found chart on canvas.chart, destroying it...');
        canvas.chart.destroy();
        canvas.chart = null;
      }
    } catch (e) {
      console.log('Canvas.chart destruction failed:', e);
    }
    
    // Wait a bit to ensure cleanup is complete
    return new Promise(resolve => setTimeout(resolve, 200));
  }
  
  // Function to create chart
  async function createChart(presentCount, lateCount, absentCount) {
    const chartContainer = document.querySelector('.summary-chart-container');
    if (!chartContainer) {
      console.error('Chart container not found');
      return;
    }
    
    // Remove existing canvas and create a new one
    const existingCanvas = document.getElementById('attendancePieChart');
    if (existingCanvas) {
      console.log('Removing existing canvas...');
      existingCanvas.remove();
    }
    
    // Create new canvas
    const newCanvas = document.createElement('canvas');
    newCanvas.id = 'attendancePieChart';
    newCanvas.width = 350;
    newCanvas.height = 350;
    newCanvas.style.maxWidth = '100%';
    
    // Insert the new canvas into the container
    chartContainer.appendChild(newCanvas);
    
    const ctx = newCanvas;
    
    console.log('Created new canvas:', ctx);
    console.log('Chart data:', { presentCount, lateCount, absentCount });
    
    // Ensure we have valid data
    const data = [
      Math.max(0, presentCount || 0),
      Math.max(0, lateCount || 0),
      Math.max(0, absentCount || 0)
    ];
    
    console.log('Final chart data array:', data);
    
    // Check if all values are zero
    if (data.every(val => val === 0)) {
      console.warn('All chart values are zero, creating empty chart');
      // Create a chart with minimal data to show "No Data" message
      data[0] = 1; // Set present to 1 to show at least something
    }
    
    try {
      attendanceChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['Present', 'Late', 'Absent'],
          datasets: [{
            data: data,
            backgroundColor: ['#27ae60', '#f39c12', '#e74c3c'],
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: { 
                color: '#2980b9', 
                font: { size: 15, weight: 'bold' },
                padding: 20
              }
            },
            tooltip: {
              enabled: true,
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                  return `${label}: ${value} (${percentage}%)`;
                }
              }
            }
          },
          animation: {
            animateRotate: true,
            animateScale: true
          }
        }
      });
      
      // Store reference on canvas element
      ctx.chart = attendanceChart;
      
      console.log('Chart created successfully with data:', { presentCount, lateCount, absentCount });
      console.log('Chart instance:', attendanceChart);
      
      // Force chart update
      attendanceChart.update();
      
    } catch (error) {
      console.error('Error creating chart:', error);
      console.error('Error details:', error.message);
      console.error('Error stack:', error.stack);
    }
  }
  
  // Function to update summary stats
  function updateSummaryStats(stats) {
    const summaryContainer = document.getElementById('summary-stats');
    if (!summaryContainer) {
      console.error('Summary stats container not found');
      return;
    }
    
    summaryContainer.innerHTML = `
      <div class="card card-accent-blue summary-card">
        <div class="summary-icon"><span class="icon-summary">📚</span></div>
        <div>
          <h3>Total Classes</h3>
          <p class="stat-number">${stats.total_classes || 0}</p>
        </div>
      </div>
      <div class="card card-accent-cyan summary-card">
        <div class="summary-icon"><span class="icon-summary">👨‍🎓</span></div>
        <div>
          <h3>Total Students</h3>
          <p class="stat-number">${stats.total_students || 0}</p>
        </div>
      </div>
      <div class="card card-accent-lightblue summary-card">
        <div class="summary-icon"><span class="icon-summary">📝</span></div>
        <div>
          <h3>Total Attendance</h3>
          <p class="stat-number">${stats.total_attendance || 0}</p>
        </div>
      </div>
      <div class="card summary-card" style="border-left: 6px solid #27ae60;">
        <div class="summary-icon"><span class="icon-summary">✅</span></div>
        <div>
          <h3>Present</h3>
          <p class="stat-number" style="color: #27ae60;">${stats.present_count || 0}</p>
        </div>
      </div>
      <div class="card summary-card" style="border-left: 6px solid #f39c12;">
        <div class="summary-icon"><span class="icon-summary">⏰</span></div>
        <div>
          <h3>Late</h3>
          <p class="stat-number" style="color: #f39c12;">${stats.late_count || 0}</p>
        </div>
      </div>
      <div class="card summary-card" style="border-left: 6px solid #e74c3c;">
        <div class="summary-icon"><span class="icon-summary">❌</span></div>
        <div>
          <h3>Absent</h3>
          <p class="stat-number" style="color: #e74c3c;">${stats.absent_count || 0}</p>
        </div>
      </div>
    `;
  }
  
  // Function to show error message
  function showError(message) {
    const summaryContainer = document.getElementById('summary-stats');
    if (summaryContainer) {
      summaryContainer.innerHTML = `
        <div class="card summary-card" style="grid-column: 1 / -1; text-align: center; color: #e74c3c;">
          <h3>Error Loading Summary Data</h3>
          <p>${message}</p>
        </div>
      `;
    }
  }
  
  // Initialize page
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, fetching summary data...');
    
    // Fetch and display summary data with cache busting
    const timestamp = new Date().getTime();
    fetch(`/attendance-system/backend/attendance/summary.php?t=${timestamp}`)
      .then(res => {
        console.log('Response status:', res.status);
        console.log('Response headers:', res.headers);
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then(data => {
        console.log('Summary data received:', data);
        console.log('Data keys:', Object.keys(data));
        
        if (data.success) {
          const stats = data;
          console.log('Stats data:', stats);
          updateSummaryStats(stats);
          
          // Create chart with correct data from backend
          const presentCount = parseInt(stats.present_count) || 0;
          const lateCount = parseInt(stats.late_count) || 0;
          const absentCount = parseInt(stats.absent_count) || 0;
          
          console.log('Creating chart with:', { presentCount, lateCount, absentCount });
          createChart(presentCount, lateCount, absentCount).catch(error => {
            console.error('Error in createChart:', error);
          });
          
        } else {
          console.error('Summary API error:', data.error);
          showError(data.error || 'Unknown error occurred');
          createChart(0, 0, 0).catch(error => {
            console.error('Error in createChart (error case):', error);
          });
        }
      })
      .catch(error => {
        console.error('Error fetching summary data:', error);
        showError('Network error or server unavailable');
        createChart(0, 0, 0).catch(error => {
          console.error('Error in createChart (catch case):', error);
        });
      });
  });
  
  // Cleanup on page unload
  window.addEventListener('beforeunload', function() {
    destroyChart();
  });
  </script>
</body>
</html>
