// Shapes shared by the Supabase backend and the in-browser demo backend.
// Field names match the database columns in supabase/schema.sql.

export type Role = 'student' | 'faculty' | 'admin'

export interface Profile {
  id: string
  username: string
  first_name: string
  last_name: string
  email: string
  role: Role
  created_at: string
}

export interface Course {
  id: number
  code: string
  title: string
  description: string
  color: string
  published: boolean
  position: number
}

export interface Module {
  id: number
  course_id: number
  title: string
  description: string
  position: number
}

export interface Attachment {
  name: string
  url: string
}

export interface Lesson {
  id: number
  module_id: number
  title: string
  content: string
  video_url: string
  attachments: Attachment[]
  minutes: number
  position: number
}

export interface Quiz {
  id: number
  module_id: number
  title: string
  description: string
  question_limit: number | null
  passing_percent: number
  time_limit_minutes: number | null
  position: number
}

export type QuestionKind = 'multiple_choice' | 'true_false'

export interface Choice {
  id: number
  question_id: number
  text: string
  is_correct: boolean
}

export interface Question {
  id: number
  quiz_id: number
  prompt: string
  kind: QuestionKind
  explanation: string
  position: number
  choices: Choice[]
}

export interface Announcement {
  id: number
  title: string
  body: string
  pinned: boolean
  created_at: string
}

export interface LessonProgress {
  user_id: string
  lesson_id: number
  completed_at: string
}

export interface QuizAttempt {
  id: number
  user_id: string
  quiz_id: number
  score: number
  total: number
  percent: number
  passed: boolean
  created_at: string
}

/** A course with everything inside it (lessons without their body text). */
export interface CourseTree extends Course {
  modules: (Module & { lessons: Lesson[]; quizzes: Quiz[] })[]
}

/** What a student sees when taking a quiz: no answer key. */
export interface QuizPaper {
  quiz: Quiz
  questions: { id: number; prompt: string; kind: QuestionKind; choices: { id: number; text: string }[] }[]
}

export interface QuizResult {
  score: number
  total: number
  percent: number
  passed: boolean
  review: {
    questionId: number
    prompt: string
    explanation: string
    chosenId: number | null
    correctId: number | null
    choices: { id: number; text: string }[]
  }[]
}

export interface SignUpInput {
  email: string
  password: string
  username: string
  first_name: string
  last_name: string
}

/** Everything the UI needs from a backend. Implemented by api/supabase.ts and api/demo.ts. */
export interface Api {
  mode: 'supabase' | 'demo'

  // auth
  currentProfile(): Promise<Profile | null>
  onAuthChange(cb: () => void): () => void
  signIn(email: string, password: string): Promise<void>
  signUp(input: SignUpInput): Promise<{ needsEmailConfirmation: boolean }>
  signOut(): Promise<void>
  sendPasswordReset(email: string): Promise<void>
  updatePassword(password: string): Promise<void>
  updateProfile(patch: Pick<Profile, 'first_name' | 'last_name' | 'username'>): Promise<void>

  // content
  listCourseTrees(): Promise<CourseTree[]>
  getLesson(id: number): Promise<Lesson>
  saveCourse(c: Partial<Course>): Promise<Course>
  deleteCourse(id: number): Promise<void>
  saveModule(m: Partial<Module>): Promise<Module>
  deleteModule(id: number): Promise<void>
  saveLesson(l: Partial<Lesson>): Promise<Lesson>
  deleteLesson(id: number): Promise<void>
  saveQuiz(q: Partial<Quiz>): Promise<Quiz>
  deleteQuiz(id: number): Promise<void>
  listQuestions(quizId: number): Promise<Question[]>
  saveQuestion(q: Partial<Omit<Question, 'choices'>> & { quiz_id: number; choices: { text: string; is_correct: boolean }[] }): Promise<void>
  deleteQuestion(id: number): Promise<void>
  uploadFile(file: File): Promise<string>

  // learning
  startQuiz(quizId: number): Promise<QuizPaper>
  submitQuiz(quizId: number, answers: Record<number, number | null>): Promise<QuizResult>
  myProgress(): Promise<{ lessons: LessonProgress[]; attempts: QuizAttempt[] }>
  setLessonComplete(lessonId: number, done: boolean): Promise<void>

  // announcements
  listAnnouncements(): Promise<Announcement[]>
  saveAnnouncement(a: Partial<Announcement>): Promise<void>
  deleteAnnouncement(id: number): Promise<void>

  // staff
  listProfiles(): Promise<Profile[]>
  setRole(userId: string, role: Role): Promise<void>
  allProgress(): Promise<{ lessons: LessonProgress[]; attempts: QuizAttempt[] }>
}
