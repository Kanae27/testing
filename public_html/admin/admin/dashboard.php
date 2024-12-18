<?php
session_start();
if(!isset($_SESSION['admin_username'])) {
    header('location: index.php');
    exit();
}
include('header.php');
?>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
        <div class="top_nav">
            <div class="nav_menu">
                <div class="nav toggle">
                  <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                </div>
            </div>
        </div>
        
        <!-- page content -->
        <div class="right_col" role="main">
          <!-- top tiles -->
          <div class="row" style="display: inline-block;" >
          <div class="tile_count">
           &nbsp;
          </div>
        </div>
          <!-- /top tiles -->

          <br />

<?php
include('../connect.php');
$result1 = $conn->query("SELECT * FROM store");
$count1 = $result1->num_rows;
$result2 = $conn->query("SELECT * FROM product");
$count2 = $result2->num_rows;
$result3 = $conn->query("SELECT * FROM login WHERE type = 'user'");
$count3 = $result3->num_rows;
$result4 = $conn->query("SELECT * FROM cart WHERE status = 'Approved'");
$count4 = $result4->num_rows;
?>

              <div class="row">
                <div class="col-md-12 col-sm-12 " style="color:#000">
                  <div class="x_panel">
                    <div class="x_title">
                      <h2>Dashboard</h2>
                      
                      <div class="clearfix"></div>
                      <div class="clearfix"></div>
<div class="row" style="margin-top:20px">
  <div class="col-sm-4">
    <div class="card" style="background:#C0EBA6">
      <div class="card-body">
      <h1  style="color:#000"><i class="fa-solid fa-list"></i>&nbsp;<?php echo $count2 ?></h1>
        <h5  style="color:#000" class="card-title">Total Number of Products</h5>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card" style="background:#C0EBA6">
      <div class="card-body">
      <h1  style="color:#000" ><i class="fa-solid fa-users"></i>&nbsp;<?php echo $count3 ?></h1>
        <h5  style="color:#000" class="card-title">Total Number of Registered Users</h5>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card" style="background:#C0EBA6">
      <div class="card-body">
      <h1  style="color:#000"><i class="fa-solid fa-square-poll-vertical"></i>&nbsp;<?php echo $count4 ?></h1>
        <h5 style="color:#000" class="card-title">Total Number of Sales</h5>
      </div>
    </div>
  </div>
</div>
<br>

 <script>
 
Highcharts.chart('con', {
    // ... your existing Highcharts code ...
});
 
Highcharts.chart('con1', {
    // ... your existing Highcharts code ...
});

Highcharts.chart('con3', {
    // ... your existing Highcharts code ...
});
 </script>      
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

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

    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    <!-- Chart.js -->
    <script src="../vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- gauge.js -->
    <script src="../vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="../vendors/iCheck/icheck.min.js"></script>
    <!-- Skycons -->
    <script src="../vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="../vendors/Flot/jquery.flot.js"></script>
    <script src="../vendors/Flot/jquery.flot.pie.js"></script>
    <script src="../vendors/Flot/jquery.flot.time.js"></script>
    <script src="../vendors/Flot/jquery.flot.stack.js"></script>
    <script src="../vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="../vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="../vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="../vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="../vendors/DateJS/build/date.js"></script>
    <!-- JQVMap -->
    <script src="../vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="../vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="../vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- Datatables -->
    <script src="../vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="../vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="../vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="../vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="../vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    <script src="../vendors/jszip/dist/jszip.min.js"></script>
    <script src="../vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="../vendors/pdfmake/build/vfs_fonts.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>
</body>
</html>