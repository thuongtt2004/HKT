#!/bin/bash

echo "========================================"
echo " COMMIT TO GITHUB - RETURN ORDER STATUS"
echo "========================================"
echo

cd "$(dirname "$0")"

echo "[1/4] Checking git status..."
git status
echo

echo "[2/4] Adding files..."
git add database/migrations/
git add database/.gitignore
git add admin_returns.php
git add admin_orders.php
git add process_order_action.php
git add track_order.php
git add HUONG_DAN_TRANG_THAI_TRA_HANG.md
git add DATABASE_FIX_GUIDE.md
git add .gitignore
echo "Done!"
echo

echo "[3/4] Committing..."
git commit -m "feat: Add return order statuses and user status column

- Add 3 new order statuses for return process:
  + Cho xac nhan tra hang (unlocked, waiting for admin)
  + Da duyet tra hang (locked, stock restored)
  + Khong dong y duyet tra hang (locked, rejected)
  
- Auto update order_status when:
  + Customer requests return -> 'Cho xac nhan tra hang'
  + Admin approves -> 'Da duyet tra hang' + restore stock
  + Admin rejects -> 'Khong dong y duyet tra hang'
  
- Lock approved/rejected return orders (cannot change status)
- Add status column to users table (for lock/unlock accounts)
- Add database migrations system with tracking
- Update UI colors for return statuses
- Fix admin_users.php missing status column error

Files changed:
- database/migrations/add_return_order_statuses.sql (new)
- database/migrations/add_users_status_column.sql (new)
- admin_returns.php (updated)
- admin_orders.php (updated)
- process_order_action.php (updated)
- track_order.php (updated)
- .gitignore (updated)

Migration required:
1. Run: database/migrations/add_return_order_statuses.sql
2. Run: database/migrations/add_users_status_column.sql"
echo "Done!"
echo

echo "[4/4] Pushing to GitHub..."
git push origin main

if [ $? -eq 0 ]; then
    echo "========================================"
    echo " SUCCESS! Changes pushed to GitHub"
    echo "========================================"
else
    echo "========================================"
    echo " ERROR! Please check your connection"
    echo " or try: git push origin master"
    echo "========================================"
fi
echo

read -p "Press Enter to continue..."
