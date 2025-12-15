# 🎯 AssessArena - Quiz Platform for EtudeSync

AssessArena is a comprehensive quiz creation and management system fully integrated into EtudeSync. Create quizzes, share them with codes, take quizzes, view results, and compete on leaderboards!

---

## ✨ Features

### 1️⃣ Create Quizzes
- **Custom Titles**: Name your quizzes
- **Time Limits**: Optional time constraints (minutes)
- **Question Shuffling**: Randomize question order for each attempt
- **MCQ Support**: 4-option multiple choice questions
- **Shareable Codes**: Auto-generated 8-character quiz codes

### 2️⃣ Take Quizzes
- **Quiz Code Entry**: Join any quiz with its code
- **Timer Display**: Real-time countdown (if time limit set)
- **Clean Interface**: Easy-to-use question cards with option buttons
- **Instant Results**: See your score immediately after submission

### 3️⃣ Results & History
- **Attempt History**: View all your past quiz attempts
- **Detailed Scores**: See score, percentage, and time taken
- **Answer Review**: Review correct vs incorrect answers
- **Performance Tracking**: Track improvement over time

### 4️⃣ Leaderboards
- **Quiz-Specific Rankings**: Top scores for each quiz
- **Global Stats**: Your performance across all quizzes
- **Speed Tracking**: Fastest completion times
- **Attempt Counts**: See how many times users have tried

### 5️⃣ My Stats Dashboard
- **Visual Stats**: Beautiful stat cards with gradients
- **Quiz Count**: Total quizzes taken
- **Total Attempts**: Cumulative attempt count
- **Average Score**: Overall performance percentage

---

## 🚀 Installation & Setup

### Step 1: Database Setup

The database schema already exists at `/sql/assessarena_schema.sql`.

**Option A: Automatic Setup (Recommended)**
1. Navigate to: `http://localhost/etudesync/public/setup_assessarena.php`
2. The script will automatically create all necessary tables
3. Follow the on-screen instructions

**Option B: Manual Setup**
1. Open phpMyAdmin or your MySQL client
2. Select the `etudesync` database
3. Import the file: `/sql/assessarena_schema.sql`

### Step 2: Verify Installation

The setup creates 3 tables:
- **`quizzes`** - Stores quiz metadata (title, code, owner, time limit, etc.)
- **`questions`** - Stores quiz questions with 4 options and correct answer
- **`attempts`** - Stores user quiz attempts with scores and answers

### Step 3: Access AssessArena

1. Log in to your EtudeSync account
2. Go to the Dashboard
3. Click on the **AssessArena** module card
4. Start creating or taking quizzes!

---

## 📁 File Structure

```
etudesync/
├── public/
│   ├── assessarena.php              # Main module page
│   ├── setup_assessarena.php        # Database setup script
│   │
│   ├── api/assessarena/
│   │   ├── quiz_create.php          # Create new quiz
│   │   ├── quiz_list.php            # List user's quizzes
│   │   ├── quiz_get.php             # Get quiz by code
│   │   ├── question_add.php         # Add question to quiz
│   │   ├── attempt_submit.php       # Submit quiz attempt
│   │   ├── attempt_history.php      # Get user's attempt history
│   │   └── leaderboard.php          # Get leaderboard data
│   │
│   └── assets/
│       ├── js/assessarena.js        # All frontend JavaScript
│       └── images/icon-assessarena.png  # Module icon
│
├── sql/
│   └── assessarena_schema.sql       # Database schema
│
└── ASSESSARENA_README.md            # This file
```

---

## 🎮 User Guide

### Creating a Quiz

1. Click **"Create Quiz"** from the AssessArena hub
2. Enter quiz details:
   - **Title**: Name your quiz (required)
   - **Time Limit**: Optional (in minutes)
   - **Shuffle Questions**: Check to randomize order
3. Click **"Create Quiz & Add Questions"**
4. Add questions one by one:
   - Question text
   - 4 options (A, B, C, D)
   - Select correct answer
5. Click **"Add Question"** for each
6. Click **"Finish Quiz"** when done
7. Share the generated **8-character code** with others!

### Taking a Quiz

1. Click **"Take Quiz"** from the AssessArena hub
2. Enter the **8-character quiz code**
3. Click **"Load Quiz"**
4. Answer all questions by clicking options
5. Click **"Submit Quiz"**
6. View your instant results with answer review

### Viewing Results

1. Click **"History"** from the AssessArena hub
2. See all your past attempts with:
   - Quiz title and code
   - Score and percentage
   - Time taken
   - Submission date

### Checking Leaderboard

1. Click **"Leaderboard"** from the AssessArena hub
2. **Option A**: Leave code blank to see your overall stats
3. **Option B**: Enter a quiz code to see that quiz's rankings
4. See rankings by best score, fastest time, and attempt count

---

## 🎨 Design System

AssessArena follows the **exact same design language** as other EtudeSync modules:

### Colors
- **Primary Gradient**: `#7c4dff` → `#47d7d3`
- **Success**: `#10B981` (80%+ scores)
- **Good**: `#3B82F6` (60-79%)
- **Average**: `#F59E0B` (40-59%)
- **Poor**: `#EF4444` (<40%)

### Typography
- **Display Font**: Poppins (800 weight for headings)
- **Body Font**: Inter (400-700 weights)
- **Sizes**: Matching FocusFlow and CollabSphere

### Components
- **Glass Morphism Cards**: `rgba(15,20,30,0.45)` with blur
- **White Content Cards**: `#fff` with shadow
- **Module Cards**: Matching dashboard grid style
- **Buttons**: Gradient primary, neutral secondary

### Spacing & Layout
- **3-column grid** on desktop (980px+)
- **2-column grid** on tablet (560-980px)
- **1-column grid** on mobile (<560px)
- **Consistent padding**: 32px cards, 20px on mobile

---

## 🔧 API Reference

### Quiz APIs

#### `POST /api/assessarena/quiz_create.php`
**Create a new quiz**

Request:
```
title: string (required)
time_limit: int (optional, minutes)
shuffle_questions: boolean (optional)
```

Response:
```json
{
  "ok": true,
  "quiz_id": 123,
  "code": "AB12CD34",
  "msg": "Quiz created successfully"
}
```

#### `GET /api/assessarena/quiz_list.php`
**Get user's created quizzes**

Response:
```json
{
  "ok": true,
  "quizzes": [
    {
      "id": 123,
      "code": "AB12CD34",
      "title": "Math Quiz",
      "time_limit_minutes": 15,
      "created_at": "2025-12-14 10:30:00",
      "question_count": 10
    }
  ]
}
```

#### `GET /api/assessarena/quiz_get.php?code=AB12CD34`
**Get quiz for taking (without correct answers)**

Response:
```json
{
  "ok": true,
  "quiz": {
    "id": 123,
    "title": "Math Quiz",
    "time_limit_minutes": 15,
    "total_questions": 10
  },
  "questions": [
    {
      "id": 456,
      "text": "What is 2+2?",
      "option_a": "3",
      "option_b": "4",
      "option_c": "5",
      "option_d": "6",
      "position": 1
    }
  ]
}
```

### Question APIs

#### `POST /api/assessarena/question_add.php`
**Add a question to a quiz**

Request:
```
quiz_id: int (required)
text: string (required)
option_a: string (required)
option_b: string (required)
option_c: string (required)
option_d: string (required)
correct_option: enum('A','B','C','D') (required)
```

Response:
```json
{
  "ok": true,
  "question_id": 456,
  "position": 1,
  "msg": "Question added successfully"
}
```

### Attempt APIs

#### `POST /api/assessarena/attempt_submit.php`
**Submit a quiz attempt**

Request:
```
quiz_id: int (required)
started_at: datetime (required)
answers[question_id]: string (A/B/C/D)
```

Response:
```json
{
  "ok": true,
  "attempt_id": "attempt_abc123",
  "score": 8,
  "total_questions": 10,
  "percentage": 80,
  "duration_seconds": 245,
  "results": [
    {
      "question_id": 456,
      "user_answer": "B",
      "correct_answer": "B",
      "is_correct": true
    }
  ]
}
```

#### `GET /api/assessarena/attempt_history.php`
**Get user's attempt history**

Response:
```json
{
  "ok": true,
  "attempts": [
    {
      "attempt_id": "attempt_abc123",
      "score": 8,
      "total_questions": 10,
      "percentage": 80,
      "duration_seconds": 245,
      "duration_formatted": "04:05",
      "submitted_at": "2025-12-14 11:00:00",
      "quiz_title": "Math Quiz",
      "quiz_code": "AB12CD34"
    }
  ]
}
```

### Leaderboard API

#### `GET /api/assessarena/leaderboard.php?quiz_code=AB12CD34`
**Get quiz-specific leaderboard**

Response:
```json
{
  "ok": true,
  "quiz_code": "AB12CD34",
  "leaderboard": [
    {
      "user_name": "John Doe",
      "best_score": 10,
      "total_questions": 10,
      "percentage": 100,
      "fastest_time": 180,
      "fastest_time_formatted": "03:00",
      "attempts_count": 3
    }
  ]
}
```

#### `GET /api/assessarena/leaderboard.php`
**Get user's global stats**

Response:
```json
{
  "ok": true,
  "user_stats": [
    {
      "title": "Math Quiz",
      "code": "AB12CD34",
      "best_score": 8,
      "total_questions": 10,
      "percentage": 80,
      "attempts_count": 2
    }
  ]
}
```

---

## 🔐 Security Features

- ✅ **Session-based authentication** - All APIs require login
- ✅ **Quiz ownership verification** - Only quiz creators can add questions
- ✅ **SQL injection prevention** - Prepared statements throughout
- ✅ **XSS protection** - All output is escaped
- ✅ **Input validation** - Server-side validation on all inputs
- ✅ **Unique quiz codes** - MD5 hash-based code generation

---

## 🎯 Future Enhancements (Optional)

**Nice-to-Have Features:**
- ✨ Quiz editing after creation
- ✨ Question deletion/reordering
- ✨ Quiz analytics (most missed questions)
- ✨ Quiz categories/tags
- ✨ Public vs private quizzes
- ✨ Quiz search functionality
- ✨ Export results to CSV
- ✨ Quiz templates
- ✨ Image support in questions
- ✨ True/False question type
- ✨ Fill-in-the-blank questions

**Advanced Features (Beyond Scope):**
- ❌ Proctoring (explicitly avoided)
- ❌ File uploads (explicitly avoided)
- ❌ Essay questions (explicitly avoided)

---

## 🐛 Troubleshooting

### Quiz code not working
- Ensure the code is exactly 8 characters
- Check if the quiz was created successfully
- Verify the quiz has at least 1 question

### Timer not showing
- Timer only shows if the quiz has a time limit set
- Check browser console for JavaScript errors

### Database errors
- Run `setup_assessarena.php` to create tables
- Check that the `etudesync` database exists
- Verify database connection in `/includes/db.php`

### Leaderboard empty
- Leaderboard requires at least one quiz attempt
- For quiz-specific leaderboard, enter valid quiz code

---

## 📞 Support

For issues or questions:
1. Check this README
2. Review the browser console for JavaScript errors
3. Check PHP error logs
4. Verify database tables exist

---

## 📄 License

AssessArena is part of the EtudeSync platform.

---

**Built with ❤️ for EtudeSync**

*Matching the exact design language and component patterns of FocusFlow, CollabSphere, and the Dashboard.*
