# Football Agent Sierra Leone - Assignment 2 (Back-End)

## Overview
Modular PHP backend for user roles and database management. Uses OOP for CRUD operations.

## Setup
1. Update config/config.php with DB credentials.
2. Run scripts/setup.php to create DB, tables, and insert data.
3. Use demo.php to test CRUD.

## Directory structure

```bash
football-agent/
├── config/
│   └── config.php          # Database configuration
├── models/
│   ├── DB.php              # Database connection class
│   ├── User.php            # User model with CRUD
│   ├── PlayerProfile.php   # PlayerProfile model with CRUD
│   ├── AgentProfile.php    # AgentProfile model with CRUD
│   ├── Club.php            # Club model with CRUD
│   └── Contract.php        # Contract model with CRUD
├── scripts/
│   └── setup.php           # Script to create DB, tables, and insert sample data
├── README.md               # Documentation
└── demo.php                # Optional demo script to test CRUD
```

## Integration with Front-End
Include models in your HTML/PHP pages for dynamic data (e.g., fetch users in contact.php).

Submitted by: M'mah Zombo

GitHub: [https://github.com/Mmah-Zombo]