 <!-- Sidebar -->
 <div class="sidebar">
            <div class="user-info">
                <h3>Admin Panel</h3>
                <p><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
                    <li><a href="packages.php"><i class="fas fa-suitcase"></i> Manage Packages</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="support.php"><i class="fas fa-headset"></i> Support Messages</a></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
</div>
