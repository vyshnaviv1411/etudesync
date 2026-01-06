# AssessArena Database Migration Guide

## 🚨 Problem Identified

Your AssessArena module was throwing these SQL errors:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'time_limit_minutes' in field list
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'code' in field list
```

**Root Cause:** The `quizzes` table in your database is missing required columns that the PHP backend code expects.

---

## ✅ Solution: Run the Migration

The database needs these columns added to the `quizzes` table:
- `code` (VARCHAR(12), UNIQUE, NOT NULL) - For unique quiz identifiers
- `time_limit_minutes` (INT, NULL) - Optional time limit for quizzes
- `shuffle_questions` (BOOLEAN, DEFAULT FALSE) - Whether to randomize questions

---

## 🔧 How to Fix (Choose ONE Method)

### **Method 1: Automatic Web Migration (RECOMMENDED)**

1. **Open your browser** and navigate to:
   ```
   http://localhost/etudesync/public/admin/migrate_assessarena.php
   ```

2. **Login** to your ÉtudeSync account (if not already logged in)

3. **The migration will run automatically** and show you:
   - ✅ What columns were added
   - ✅ How many quiz codes were generated
   - ✅ Final table structure
   - 🎉 Success confirmation

4. **Go to AssessArena** and test creating a quiz!

---

### **Method 2: SQL File in phpMyAdmin**

1. **Open phpMyAdmin** in your browser:
   ```
   http://localhost/phpmyadmin
   ```

2. **Select the `etudesync` database** from the left sidebar

3. **Click the "SQL" tab** at the top

4. **Copy the contents** of `fix_assessarena_simple.sql` (located in `/sql/` folder)

5. **Paste into the SQL editor** and click "Go"

6. **Check for success messages** - ignore "Duplicate column" errors if columns already exist

---

### **Method 3: MySQL Command Line**

1. **Open terminal/command prompt**

2. **Login to MySQL:**
   ```bash
   mysql -u root -p
   ```

3. **Run the migration:**
   ```bash
   USE etudesync;
   SOURCE /path/to/etudesync/sql/fix_assessarena_simple.sql;
   ```

4. **Exit MySQL:**
   ```bash
   EXIT;
   ```

---

## 🧪 Verify the Fix

After running the migration, test these operations:

### **1. Create a Quiz**
- Go to AssessArena
- Click "Create New Quiz"
- Enter a title and optionally set a time limit
- Submit
- ✅ **Should succeed without errors**

### **2. Load a Quiz**
- Note the quiz code (e.g., "A3F7B2D8")
- Share the code
- Try loading the quiz using the code
- ✅ **Should load successfully**

### **3. Add Questions**
- Add questions to your quiz
- Set correct answers
- ✅ **Should save without errors**

### **4. Take a Quiz**
- Use the quiz code to take the quiz
- Submit answers
- ✅ **Should calculate score correctly**

---

## 📊 What the Migration Does

### **Step 1:** Checks if `quizzes` table exists
- If not, creates it with full schema

### **Step 2:** Adds missing columns
- `code` - Unique identifier for each quiz
- `time_limit_minutes` - Optional timer (NULL allowed)
- `shuffle_questions` - Boolean flag for randomization

### **Step 3:** Generates codes for existing quizzes
- Any quiz without a code gets a unique 8-character code
- Format: `UPPERCASE` alphanumeric (e.g., "D4A9B2F1")

### **Step 4:** Adds database indexes
- `idx_code` on `code` column for fast lookups
- `idx_owner` on `owner_id` for filtering by creator

### **Step 5:** Sets constraints
- Makes `code` column NOT NULL and UNIQUE
- Ensures data integrity

---

## 🛡️ Safety Features

✅ **Idempotent:** Safe to run multiple times
✅ **Non-destructive:** Does not delete or modify existing data
✅ **Backward compatible:** Existing quizzes remain intact
✅ **Generates missing data:** Auto-creates codes for old quizzes
✅ **Transaction safe:** Uses prepared statements

---

## 🐛 Troubleshooting

### **Error: "Access denied"**
- Make sure you're logged into ÉtudeSync
- The web migration requires authentication

### **Error: "Table 'quizzes' doesn't exist"**
- The migration will create it automatically
- Or run the full schema: `assessarena_schema_fixed.sql`

### **Error: "Duplicate column name"**
- This is **normal** - means the column already exists
- The migration will skip it
- Not an error, just a warning

### **Quiz codes not showing**
- Refresh the page after migration
- Codes are auto-generated during migration
- Check database: `SELECT id, code, title FROM quizzes;`

---

## 📝 Final Database Structure

After migration, your `quizzes` table will have:

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| id | INT | NO | PRI | NULL | auto_increment |
| **code** | VARCHAR(12) | NO | UNI | NULL | |
| owner_id | INT | NO | MUL | NULL | |
| title | VARCHAR(200) | NO | | NULL | |
| **time_limit_minutes** | INT | YES | | NULL | |
| **shuffle_questions** | BOOLEAN | YES | | FALSE | |
| created_at | TIMESTAMP | NO | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NO | | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |

---

## ✅ Migration Complete!

Once the migration runs successfully:

1. ✅ **Quiz creation** will work without SQL errors
2. ✅ **Quiz codes** will be unique and functional
3. ✅ **Time limits** can be set (or left blank)
4. ✅ **All CRUD operations** will work properly
5. ✅ **AssessArena** is fully operational

Navigate to **AssessArena** and start creating quizzes! 🎉

---

## 📞 Support

If you encounter issues after running the migration:
1. Check the browser console for errors
2. Check the network tab for API responses
3. Verify database structure: `DESCRIBE quizzes;`
4. Check for quiz codes: `SELECT code FROM quizzes LIMIT 10;`

The migration script logs all operations - review the output for details.
