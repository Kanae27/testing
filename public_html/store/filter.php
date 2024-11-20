<?php

include('./header.php');
?>

<!-- CSS Dependencies -->
<link href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css" rel="stylesheet">

<style>
.card {
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.card-body {
    padding: 15px;
}

.table-container {
    margin-top: 30px;
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dataTables_wrapper {
    padding: 20px 0;
}

.dt-buttons .btn {
    margin-right: 5px;
}

.chart-container {
    margin-top: 30px;
    padding: 20px;
    background: #fff;
    border-radius: 5px;
}

.charts-row {
    margin: 0 -10px 20px -10px;
}

.chart-container {
    background: #fff;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 10px;
}

@media (max-width: 768px) {
    .chart-container {
        margin-bottom: 15px;
    }
}

.list-unstyled {
    margin-bottom: 0;
}
.list-unstyled li {
    margin-bottom: 3px;
}
.list-unstyled li:last-child {
    margin-bottom: 0;
}

.form-group {
    margin-bottom: 15px;
}

.btn-primary {
    margin-top: 5px;
}

/* Make month filter same height as date filter */
input[type="month"],
input[type="date"] {
    height: 38px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-md-4 {
        margin-top: 20px;
    }
}
</style>

<!-- Main Content -->
<div class="right_col" role="main">
    <!-- Add User Info Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                
               
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row">
        <div class="col-md-8">
            <!-- Charts and other content will go here -->
            <div class="row charts-row">
                <!-- Monthly Sales Bar Chart -->
                <div class="col-12">
                    <div class="chart-container">
                        <div id="salesChart" style="width:100%; height:400px"></div>
                        <table id="graphData" class="display" style="display:none">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Number of Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Initialize months array at the start
                                $current_year = date('Y');
                                $months = array_fill(1, 12, 0);

                                // Monthly Sales Chart Query
                                $monthly_query = "
                                    SELECT 
                                        MONTH(timestamp) as month,
                                        SUM(total_amount) as total
                                    FROM (
                                        SELECT 
                                            c.timestamp,
                                            (c.quantity * p.price) as total_amount
                                        FROM cart c
                                        JOIN product p ON c.product = p.id
                                        WHERE c.status != 'heart'
                                        AND c.status != 'cart'
                                        AND YEAR(c.timestamp) = $current_year
                                        
                                        UNION ALL
                                        
                                        SELECT 
                                            ci.timestamp,
                                            (ci.quantity * p.price) as total_amount
                                        FROM cart_items ci
                                        JOIN product p ON ci.product_id = p.id
                                        WHERE ci.invoice IS NOT NULL
                                        AND ci.status != 'heart'
                                        AND ci.status != 'cart'
                                        AND YEAR(ci.timestamp) = $current_year
                                    ) combined_sales
                                    GROUP BY MONTH(timestamp)
                                    ORDER BY month
                                ";

                                // Filter handling for monthly chart
                                if(isset($_POST['by_date'])) {
                                    $filter_date = $_POST['date'];
                                    $monthly_query = str_replace(
                                        "WHERE c.status != 'heart'",
                                        "WHERE DATE(c.timestamp) = '$filter_date' AND c.status != 'heart'",
                                        $monthly_query
                                    );
                                    $monthly_query = str_replace(
                                        "WHERE ci.invoice IS NOT NULL",
                                        "WHERE DATE(ci.timestamp) = '$filter_date' AND ci.invoice IS NOT NULL",
                                        $monthly_query
                                    );
                                    
                                } elseif(isset($_POST['by_month_range'])) {
                                    $start_date = $_POST['start_date'];
                                    $end_date = $_POST['end_date'];
                                    $monthly_query = str_replace(
                                        "WHERE c.status != 'heart'",
                                        "WHERE DATE(c.timestamp) BETWEEN '$start_date' AND '$end_date' AND c.status != 'heart'",
                                        $monthly_query
                                    );
                                    $monthly_query = str_replace(
                                        "WHERE ci.invoice IS NOT NULL",
                                        "WHERE DATE(ci.timestamp) BETWEEN '$start_date' AND '$end_date' AND ci.invoice IS NOT NULL",
                                        $monthly_query
                                    );
                                    
                                } elseif(isset($_POST['by_year'])) {
                                    $year = $_POST['year'];
                                    $current_year = $year; // Override current year with selected year
                                    $monthly_query = str_replace(
                                        "YEAR(c.timestamp) = $current_year",
                                        "YEAR(c.timestamp) = $year",
                                        $monthly_query
                                    );
                                    $monthly_query = str_replace(
                                        "YEAR(ci.timestamp) = $current_year",
                                        "YEAR(ci.timestamp) = $year",
                                        $monthly_query
                                    );
                                }

                                // Execute monthly query
                                $result = $conn->query($monthly_query);
                                if ($result === false) {
                                    error_log("Monthly query error: " . $conn->error);
                                } else {
                                    while($row = $result->fetch_assoc()) {
                                        $months[$row['month']] = floatval($row['total']);
                                    }
                                }

                                // Debug output
                                echo "<!-- Monthly Sales Data: " . json_encode($months) . " -->";
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Most Purchased Products Pie Chart -->
                <div class="col-12">
                    <div class="chart-container">
                        <div id="productPieChart" style="width:100%; height:400px"></div>
                        <?php
                        // Pie Chart Query
                        $pie_query = "
                            SELECT 
                                p.item as name,
                                SUM(c.quantity) as total_quantity,
                                SUM(c.quantity * p.price) as total_sales
                            FROM (
                                SELECT 
                                    product as product_id, 
                                    quantity,
                                    timestamp
                                FROM cart
                                WHERE status != 'heart'
                                AND status != 'cart'
                                
                                UNION ALL
                                
                                SELECT 
                                    product_id, 
                                    quantity,
                                    timestamp
                                FROM cart_items 
                                WHERE invoice IS NOT NULL
                                AND status != 'heart'
                                AND status != 'cart'
                            ) c
                            JOIN product p ON c.product_id = p.id
                            GROUP BY p.id, p.item
                            ORDER BY total_quantity DESC
                            LIMIT 5
                        ";

                        // Filter handling for pie chart
                        if(isset($_POST['by_date'])) {
                            $filter_date = $_POST['date'];
                            $pie_query = str_replace(
                                "WHERE status != 'heart'",
                                "WHERE DATE(timestamp) = '$filter_date' AND status != 'heart'",
                                $pie_query
                            );
                            
                        } elseif(isset($_POST['by_month_range'])) {
                            $start_date = $_POST['start_date'];
                            $end_date = $_POST['end_date'];
                            $pie_query = str_replace(
                                "WHERE status != 'heart'",
                                "WHERE DATE(timestamp) BETWEEN '$start_date' AND '$end_date' AND status != 'heart'",
                                $pie_query
                            );
                            
                        } elseif(isset($_POST['by_year'])) {
                            $year = $_POST['year'];
                            $pie_query = str_replace(
                                "WHERE status != 'heart'",
                                "WHERE YEAR(timestamp) = '$year' AND status != 'heart'",
                                $pie_query
                            );
                            
                        }

                        // Execute pie chart query
                        $pie_result = $conn->query($pie_query);
                        $pie_data = array();

                        if ($pie_result === false) {
                            error_log("Pie chart query error: " . $conn->error);
                        } else {
                            while($row = $pie_result->fetch_assoc()) {
                                $pie_data[] = array(
                                    'name' => $row['name'],
                                    'y' => intval($row['total_quantity']),
                                    'sales' => number_format($row['total_sales'], 2)
                                );
                            }
                        }

                        // If no data, add dummy data to prevent JavaScript errors
                        if (empty($pie_data)) {
                            $pie_data[] = array(
                                'name' => 'No Data',
                                'y' => 0,
                                'sales' => '0.00'
                            );
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters on the right -->
        <div class="col-md-4">
            <!-- Filter by Date -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Filter by Date</h5>
                    <form action="filter.php" method="POST">
                        <div class="form-group">
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <button type="submit" name="by_date" class="btn btn-primary w-100">Apply Filter</button>
                    </form>
                </div>
            </div>
            
            <!-- Filter by Month -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Filter by Month</h5>
                    <form action="filter.php" method="POST">
                        <div class="form-group mb-2">
                            <input type="month" name="start_date" class="form-control" required>
                        </div>
                        <button type="submit" name="by_month" class="btn btn-primary w-100">Apply Filter</button>
                    </form>
                </div>
            </div>
            
            <!-- Filter by Year -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Filter by Year</h5>
                    <form action="filter.php" method="POST">
                        <div class="form-group">
                            <input type="number" name="year" class="form-control" placeholder="Enter Year" required>
                        </div>
                        <button type="submit" name="by_year" class="btn btn-primary w-100">Apply Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- After filter section and before purchase list -->
    <div class="row mb-3">
        <?php
        // Count POS transactions
        $pos_query = "SELECT COUNT(*) as pos_count FROM cart WHERE invoice IS NOT NULL AND name = 'pos'";
        $pos_result = $conn->query($pos_query);
        $pos_count = $pos_result->fetch_assoc()['pos_count'];
        ?>
       
    </div>

    <!-- Sales Table -->
   

        <h2>Purchase List</h2>
        <table id="salesTable" class="table table-striped">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Name</th>
                    <th>Contact Number</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Item/Quantity</th>
                    <th>Total Price</th>
                    <th>Date Purchased</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $conn = mysqli_connect("localhost", "root", "", "daniel");
                // Check both cart_items and cart tables
                $check_cart_items = "SELECT COUNT(*) as count FROM cart_items WHERE invoice IS NOT NULL";
                $check_cart = "SELECT COUNT(*) as count FROM cart WHERE status = 'Delivered'";
                
                $cart_items_result = $conn->query($check_cart_items);
                $cart_result = $conn->query($check_cart);
                
                $cart_items_count = $cart_items_result->fetch_assoc()['count'];
                $cart_count = $cart_result->fetch_assoc()['count'];
                
                echo "<!-- Debug: Found {$cart_items_count} records in cart_items and {$cart_count} records in cart -->";
                echo "<!-- Debug: Total records: " . ($cart_items_count + $cart_count) . " -->";

                // Debug: Show both table structures
                echo "<!-- cart_items Table Structure: -->";
                $table_info = $conn->query("DESCRIBE cart_items");
                while($row = $table_info->fetch_assoc()) {
                    echo "<!-- Field: {$row['Field']}, Type: {$row['Type']} -->";
                }

                echo "<!-- cart Table Structure: -->";
                $table_info = $conn->query("DESCRIBE cart");
                while($row = $table_info->fetch_assoc()) {
                    echo "<!-- Field: {$row['Field']}, Type: {$row['Type']} -->";
                }

                // Base purchase query
                $purchase_query = "
                    (SELECT DISTINCT 
                        ci.invoice,
                        ci.username as name,
                        NULL as contact,
                        NULL as address,
                        ci.timestamp,
                        ci.status,
                        'cart_items' as source
                    FROM cart_items ci
                    WHERE ci.invoice IS NOT NULL
                    AND ci.status != 'heart'
                    AND ci.status != 'cart'
                    AND 1=1)  -- Add placeholder for filter conditions
                    
                    UNION ALL
                    
                    (SELECT DISTINCT 
                        c.invoice,
                        c.name,
                        c.contact,
                        c.address,
                        c.timestamp,
                        c.status,
                        'cart' as source
                    FROM cart c
                    WHERE c.status != 'heart'
                    AND c.status != 'cart'
                    AND 1=1)  -- Add placeholder for filter conditions
                    
                    ORDER BY timestamp DESC
                ";

                // Filter handling
                if(isset($_POST['by_date'])) {
                    $filter_date = $_POST['date'];
                    $where_clause = "DATE(timestamp) = '$filter_date'";
                    
                    // Modify queries with filter
                    $purchase_query = str_replace(
                        'AND 1=1)',
                        "AND $where_clause)",
                        $purchase_query
                    );
                    
                } elseif(isset($_POST['by_month_range'])) {
                    $start_date = $_POST['start_date'];
                    $end_date = $_POST['end_date'];
                    $where_clause = "DATE(timestamp) BETWEEN '$start_date' AND '$end_date'";
                    
                    // Modify queries with filter
                    $purchase_query = str_replace(
                        'AND 1=1)',
                        "AND $where_clause)",
                        $purchase_query
                    );
                    
                } elseif(isset($_POST['by_year'])) {
                    $year = $_POST['year'];
                    $where_clause = "YEAR(timestamp) = '$year'";
                    
                    // Modify queries with filter
                    $purchase_query = str_replace(
                        'AND 1=1)',
                        "AND $where_clause)",
                        $purchase_query
                    );
                    
                }

                $purchases_result = $conn->query($purchase_query);

                if (!$purchases_result) {
                    echo "Query error: " . $conn->error;
                } else {
                    while ($row = $purchases_result->fetch_assoc()) {
                        $invoice = $row['invoice'];
                        $source = $row['source'];
                        
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($invoice) . '</td>';
                        echo '<td>' . htmlspecialchars($row['name'] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($row['contact'] ?? 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($row['address'] ?? 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($row['status'] ?? 'N/A') . '</td>';
                        
                        // Get items based on source
                        echo '<td><ul class="list-unstyled">';
                        if ($source == 'cart_items') {
                            $items_query = "
                                SELECT ci.quantity, p.item, p.price, p.type_quantity
                                FROM cart_items ci
                                JOIN product p ON ci.product_id = p.id
                                WHERE ci.invoice = ?
                            ";
                        } else {
                            $items_query = "
                                SELECT c.quantity, p.item, p.price, p.type_quantity
                                FROM cart c
                                JOIN product p ON c.product = p.id
                                WHERE c.invoice = ?
                            ";
                        }
                        
                        $stmt = $conn->prepare($items_query);
                        $stmt->bind_param('s', $invoice);
                        $stmt->execute();
                        $items_result = $stmt->get_result();
                        
                        $total = 0;
                        
                        if ($items_result) {
                            while ($item = $items_result->fetch_assoc()) {
                                $quantity = $item['quantity'];
                                $subtotal = $quantity * $item['price'];
                                $total += $subtotal;
                                
                                echo '<li>' . 
                                     htmlspecialchars($item['item']) . ' × ' . 
                                     $quantity . ' ' . 
                                     htmlspecialchars($item['type_quantity'] ?? 'pcs') . 
                                     ' (₱' . number_format($item['price'], 2) . ' each)' .
                                     '</li>';
                            }
                        }
                        echo '</ul></td>';
                        
                        echo '<td>₱' . number_format($total, 2) . '</td>';
                        echo '<td>' . date('F d, Y', strtotime($row['timestamp'])) . '</td>';
                        echo '</tr>';
                    }
                }

                // Add debug counts
                $count_cart = $conn->query("SELECT COUNT(*) as count FROM cart WHERE status = 'Delivered'")->fetch_assoc()['count'];
                $count_cart_items = $conn->query("SELECT COUNT(*) as count FROM cart_items WHERE invoice IS NOT NULL")->fetch_assoc()['count'];
                echo "<!-- Debug: Found {$count_cart} records in cart and {$count_cart_items} records in cart_items -->";
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>

<!-- Add Highcharts scripts before your existing scripts -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<!-- Add this to your existing script section -->
<script>
$(document).ready(function() {
    $('#salesTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        searching: false,
        ordering: true,
        info: false,
        dom: '<"top"B>rt<"bottom"p>',
        buttons: [
            {
                extend: 'collection',
                className: 'btn btn-primary',
                buttons: ['copy', 'excel', 'pdf', 'print']
            }
        ],
        language: {
            paginate: {
                previous: '«',
                next: '»'
            }
        }
    });
});

// Monthly Sales Chart
Highcharts.chart('salesChart', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Monthly Sales Report <?php echo $current_year; ?>'
    },
    xAxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        crosshair: true
    },
    yAxis: {
        title: {
            text: 'Total Sales (₱)'
        },
        labels: {
            formatter: function() {
                return '₱' + Highcharts.numberFormat(this.value, 2);
            }
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="padding:0"><b>₱{point.y:.2f}</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    series: [{
        name: 'Monthly Sales',
        data: <?php echo json_encode(array_values($months)); ?>
    }]
});

// Product Pie Chart
Highcharts.chart('productPieChart', {
    chart: {
        type: 'pie'
    },
    title: {
        text: 'Most Purchased Products'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b><br>' +
                    'Quantity: {point.y}<br>' +
                    'Total Sales: ₱{point.sales}'
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f}%'
            }
        }
    },
    series: [{
        name: 'Products',
        colorByPoint: true,
        data: <?php echo json_encode($pie_data); ?>
    }]
});
</script>

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            Daniel and Marilyn's General Merchandise - 2024
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div> 
  </body>
</html>
