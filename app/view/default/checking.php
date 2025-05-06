<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $data['title']; ?></title>
  <style>
    /* Center the spinner vertically & horizontally */
    .spinner-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh; /* full viewport height */
      background: #f9f9f9; /* optional background color */
    }
    /* Spinner style */
    .spinner {
      width: 80px;
      height: 80px;
      border: 10px solid #f3f3f3;
      border-top: 10px solid #3498db;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      0%   { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
  <div class="spinner-container">
    <div class="spinner"></div>
  </div>
  
  <script>
    // After 3.5 seconds, redirect to reservation.php
    setTimeout(() => {
      window.location.href = "reservation";
    }, 3500);
  </script>
</body>
</html>
