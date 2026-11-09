import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { ArrowLeft, CheckCircle2, Circle, Eye, Pencil, Plus, Save, Trash2, X } from 'lucide-react'
import { api } from '../../lib/api'
import { useAction, useAsync } from '../../lib/hooks'
import type { Question, QuestionKind, Quiz } from '../../lib/types'
import { Badge, Button, ButtonLink, Card, Empty, ErrorBox, Field, Input, Modal, PageHeader, Select, Spinner, Textarea, confirmDelete } from '../../components/ui'

type Draft = { id?: number; prompt: string; kind: QuestionKind; explanation: string; choices: { text: string; is_correct: boolean }[] }

const blank = (): Draft => ({
  prompt: '', kind: 'multiple_choice', explanation: '',
  choices: [{ text: '', is_correct: true }, { text: '', is_correct: false }, { text: '', is_correct: false }, { text: '', is_correct: false }],
})
const trueFalse = (correctIsTrue = true) => [{ text: 'True', is_correct: correctIsTrue }, { text: 'False', is_correct: !correctIsTrue }]

export default function QuizEditor() {
  const quizId = Number(useParams().quizId)
  const loaded = useAsync(() => Promise.all([api.listCourseTrees(), api.listQuestions(quizId)]), [quizId])
  const [quiz, setQuiz] = useState<Quiz | null>(null)
  const [draft, setDraft] = useState<Draft | null>(null)
  const { busy, run } = useAction()

  const found = loaded.data?.[0].flatMap((c) => c.modules.flatMap((m) => m.quizzes.map((q) => ({ q, c })))).find((x) => x.q.id === quizId)
  useEffect(() => {
    if (found) setQuiz(found.q)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loaded.data])

  if (loaded.loading && !loaded.data) return <Spinner />
  if (loaded.error) return <ErrorBox message={loaded.error} onRetry={loaded.reload} />
  if (!found) return <ErrorBox message="Quiz not found." />
  if (!quiz) return <Spinner />
  const course = found.c
  const questions = loaded.data![1]

  const saveSettings = () =>
    run(async () => {
      if (!quiz.title.trim()) throw new Error('Title is required')
      await api.saveQuiz({
        id: quiz.id, title: quiz.title.trim(), description: quiz.description,
        question_limit: quiz.question_limit || null, passing_percent: Math.min(100, Math.max(0, quiz.passing_percent)),
        time_limit_minutes: quiz.time_limit_minutes || null,
      })
      loaded.reload()
    }, 'Quiz settings saved')

  const saveDraft = () =>
    run(async () => {
      if (!draft) return
      const choices = draft.choices.filter((c) => c.text.trim())
      if (!draft.prompt.trim()) throw new Error('Write the question first')
      if (choices.length < 2) throw new Error('Add at least two answers')
      if (choices.filter((c) => c.is_correct).length !== 1) throw new Error('Mark exactly one answer as correct')
      await api.saveQuestion({
        id: draft.id, quiz_id: quizId, prompt: draft.prompt.trim(), kind: draft.kind, explanation: draft.explanation.trim(),
        position: draft.id ? questions.find((q) => q.id === draft.id)?.position ?? 0 : questions.length, choices,
      })
      setDraft(null)
      loaded.reload()
    }, 'Question saved')

  const edit = (q: Question) => setDraft({ id: q.id, prompt: q.prompt, kind: q.kind, explanation: q.explanation, choices: q.choices.map((c) => ({ text: c.text, is_correct: c.is_correct })) })
  const limitTooHigh = quiz.question_limit != null && quiz.question_limit > questions.length

  return (
    <>
      <Link to={`/manage/courses/${course.id}`} className="mb-4 inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-800 dark:hover:text-stone-200">
        <ArrowLeft className="size-4" /> {course.title}
      </Link>
      <PageHeader eyebrow="Edit quiz" title={quiz.title}
        actions={<ButtonLink variant="secondary" to={`/courses/${course.id}/quizzes/${quiz.id}`}><Eye className="size-4" /> Try it</ButtonLink>} />

      <div className="grid gap-6 lg:grid-cols-[20rem_1fr]">
        <Card className="h-fit space-y-4 p-6">
          <h2 className="font-semibold">Settings</h2>
          <Field label="Title">{(id) => <Input id={id} value={quiz.title} onChange={(e) => setQuiz({ ...quiz, title: e.target.value })} />}</Field>
          <Field label="Instructions">{(id) => <Textarea id={id} rows={2} value={quiz.description} onChange={(e) => setQuiz({ ...quiz, description: e.target.value })} />}</Field>
          <Field label="Questions per attempt" hint={`Leave empty to ask all ${questions.length}. Questions are picked at random.`}>
            {(id) => <Input id={id} type="number" min={1} value={quiz.question_limit ?? ''} onChange={(e) => setQuiz({ ...quiz, question_limit: e.target.value ? Number(e.target.value) : null })} />}
          </Field>
          {limitTooHigh && <p className="text-xs text-amber-700">Only {questions.length} questions exist, so students will get all of them.</p>}
          <Field label="Passing score (%)">{(id) => <Input id={id} type="number" min={0} max={100} value={quiz.passing_percent} onChange={(e) => setQuiz({ ...quiz, passing_percent: Number(e.target.value) })} />}</Field>
          <Field label="Time limit (minutes)" hint="Leave empty for no limit.">
            {(id) => <Input id={id} type="number" min={1} value={quiz.time_limit_minutes ?? ''} onChange={(e) => setQuiz({ ...quiz, time_limit_minutes: e.target.value ? Number(e.target.value) : null })} />}
          </Field>
          <Button busy={busy} onClick={saveSettings} className="w-full"><Save className="size-4" /> Save settings</Button>
        </Card>

        <div>
          <div className="mb-4 flex items-center justify-between">
            <h2 className="font-display text-xl font-bold">Questions <span className="text-stone-400">({questions.length})</span></h2>
            <Button onClick={() => setDraft(blank())}><Plus className="size-4" /> Add question</Button>
          </div>
          {questions.length === 0 ? <Empty icon={<Plus className="size-8" />} title="No questions yet">Add multiple-choice or true/false questions.</Empty> : (
            <div className="space-y-3">
              {questions.map((q, i) => (
                <Card key={q.id} className="p-5">
                  <div className="flex items-start gap-3">
                    <span className="grid size-7 shrink-0 place-items-center rounded-lg bg-stone-100 text-sm font-bold dark:bg-stone-800">{i + 1}</span>
                    <div className="min-w-0 flex-1">
                      <p className="font-semibold">{q.prompt}</p>
                      <div className="mt-1"><Badge tone={q.kind === 'true_false' ? 'sky' : 'stone'}>{q.kind === 'true_false' ? 'True / False' : 'Multiple choice'}</Badge></div>
                      <ul className="mt-3 grid gap-1 text-sm sm:grid-cols-2">
                        {q.choices.map((c) => (
                          <li key={c.id} className={`flex items-center gap-2 ${c.is_correct ? 'font-semibold text-emerald-700 dark:text-emerald-400' : 'text-stone-500'}`}>
                            {c.is_correct ? <CheckCircle2 className="size-4" /> : <Circle className="size-4" />} {c.text}
                          </li>
                        ))}
                      </ul>
                      {q.explanation && <p className="mt-2 text-sm text-stone-500">💡 {q.explanation}</p>}
                    </div>
                    <Button variant="ghost" className="px-2" aria-label="Edit question" onClick={() => edit(q)}><Pencil className="size-4" /></Button>
                    <Button variant="ghost" className="px-2 text-red-600" aria-label="Delete question"
                      onClick={() => confirmDelete('this question') && run(async () => { await api.deleteQuestion(q.id); loaded.reload() }, 'Question deleted')}>
                      <Trash2 className="size-4" />
                    </Button>
                  </div>
                </Card>
              ))}
            </div>
          )}
        </div>
      </div>

      <Modal open={!!draft} onClose={() => setDraft(null)} title={draft?.id ? 'Edit question' : 'New question'} wide>
        {draft && (
          <div className="space-y-4">
            <Field label="Question">{(id) => <Textarea id={id} rows={2} value={draft.prompt} onChange={(e) => setDraft({ ...draft, prompt: e.target.value })} />}</Field>
            <Field label="Type">
              {(id) => (
                <Select id={id} value={draft.kind}
                  onChange={(e) => {
                    const kind = e.target.value as QuestionKind
                    setDraft({ ...draft, kind, choices: kind === 'true_false' ? trueFalse() : blank().choices })
                  }}>
                  <option value="multiple_choice">Multiple choice</option>
                  <option value="true_false">True / False</option>
                </Select>
              )}
            </Field>
            <fieldset className="space-y-2">
              <legend className="mb-1 text-sm font-medium">Answers <span className="font-normal text-stone-500">— click the circle to mark the correct one</span></legend>
              {draft.choices.map((c, i) => (
                <div key={i} className="flex items-center gap-2">
                  <button type="button" aria-label={`Mark answer ${i + 1} correct`} aria-pressed={c.is_correct}
                    onClick={() => setDraft({ ...draft, choices: draft.choices.map((x, j) => ({ ...x, is_correct: j === i })) })}
                    className={c.is_correct ? 'text-emerald-600' : 'text-stone-300 hover:text-stone-500'}>
                    {c.is_correct ? <CheckCircle2 className="size-6" /> : <Circle className="size-6" />}
                  </button>
                  <Input value={c.text} disabled={draft.kind === 'true_false'} placeholder={`Answer ${i + 1}`}
                    onChange={(e) => setDraft({ ...draft, choices: draft.choices.map((x, j) => (j === i ? { ...x, text: e.target.value } : x)) })} />
                  {draft.kind === 'multiple_choice' && draft.choices.length > 2 && (
                    <Button variant="ghost" className="px-2" aria-label="Remove answer" onClick={() => setDraft({ ...draft, choices: draft.choices.filter((_, j) => j !== i) })}>
                      <X className="size-4" />
                    </Button>
                  )}
                </div>
              ))}
              {draft.kind === 'multiple_choice' && draft.choices.length < 6 && (
                <Button variant="ghost" onClick={() => setDraft({ ...draft, choices: [...draft.choices, { text: '', is_correct: false }] })}><Plus className="size-4" /> Add answer</Button>
              )}
            </fieldset>
            <Field label="Explanation (shown after submitting)">{(id) => <Textarea id={id} rows={2} value={draft.explanation} onChange={(e) => setDraft({ ...draft, explanation: e.target.value })} />}</Field>
            <div className="flex justify-end gap-2">
              <Button variant="secondary" onClick={() => setDraft(null)}>Cancel</Button>
              <Button busy={busy} onClick={saveDraft}>Save question</Button>
            </div>
          </div>
        )}
      </Modal>
    </>
  )
}
