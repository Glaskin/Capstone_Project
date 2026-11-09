import type { Api, QuestionKind } from './types'

// Starter content. Loaded automatically in Demo mode, and by the "Add sample courses"
// button on the staff Courses page when using Supabase. Edit freely.

type SeedQuestion = { prompt: string; kind?: QuestionKind; answers: string[]; correct: number; explanation: string }
type SeedCourse = {
  code: string
  title: string
  description: string
  color: string
  modules: {
    title: string
    description: string
    lessons: { title: string; minutes: number; video_url?: string; content: string }[]
    quiz?: { title: string; description: string; question_limit: number | null; passing_percent: number; time_limit_minutes: number | null; questions: SeedQuestion[] }
  }[]
}

const tf = (prompt: string, isTrue: boolean, explanation: string): SeedQuestion => ({
  prompt, kind: 'true_false', answers: ['True', 'False'], correct: isTrue ? 0 : 1, explanation,
})

export const SEED: SeedCourse[] = [
  {
    code: 'PAES 101',
    title: 'Agricultural Machinery Safety',
    description: 'How machines are designed to protect operators: guards, shields, controls and warning signs.',
    color: 'emerald',
    modules: [
      {
        title: 'Hazards and Guarding',
        description: 'Recognize the moving parts that injure people and the guards that stop them.',
        lessons: [
          {
            title: 'Why machinery safety matters',
            minutes: 6,
            content: `Farm machines concentrate a lot of power in moving parts that sit close to the operator. Most injuries come from a few repeat situations:

- **Entanglement** — clothing or hair caught by a rotating shaft, such as a power take-off (PTO) driveline.
- **Crushing and pinching** — between two moving parts, or between a machine and the ground.
- **Cutting and shearing** — blades, cutter bars and augers.
- **Thrown objects** — stones and debris ejected by mowers and threshers.
- **Overturning** — tractors tipping on slopes or ditches.

> A safety standard does not make work safe by itself. It makes sure the *machine* gives the operator a fair chance — the operator still has to use it correctly.

### Key idea
Good design removes a hazard first, guards it second, and only *warns* about it when neither is possible.`,
          },
          {
            title: 'Guards, shields and safe distances',
            minutes: 8,
            content: `A **guard** is a physical barrier between a person and a hazard. Common types:

| Type | Example | Notes |
|---|---|---|
| Fixed guard | Belt and pulley cover | Needs a tool to remove |
| Interlocked guard | Thresher drum cover | Stops the machine when opened |
| Adjustable guard | Saw blade cover | Set to the work piece |

### Rules of thumb
1. A guard must be strong enough not to bend into the moving part.
2. Openings must be small enough that fingers cannot reach the hazard.
3. Guards that need regular removal for maintenance should stay attached (hinged) so they are not lost.
4. **Never run a machine with a guard removed.**`,
          },
        ],
        quiz: {
          title: 'Hazards & Guarding Check',
          description: 'Five random questions. You need 70% to pass.',
          question_limit: 5,
          passing_percent: 70,
          time_limit_minutes: 10,
          questions: [
            { prompt: 'Which hazard is most associated with a rotating PTO shaft?', answers: ['Entanglement', 'Overturning', 'Thrown objects', 'Noise'], correct: 0, explanation: 'Loose clothing wraps around a spinning shaft in a fraction of a second.' },
            { prompt: 'What is the preferred order of safety measures?', answers: ['Warn, guard, eliminate', 'Eliminate, guard, warn', 'Guard, warn, eliminate', 'Warn only'], correct: 1, explanation: 'Remove the hazard if possible, guard it if not, and warn only as a last resort.' },
            { prompt: 'An interlocked guard…', answers: ['Is welded shut', 'Stops the machine when opened', 'Is optional', 'Only applies to tractors'], correct: 1, explanation: 'The interlock cuts power when the guard is opened.' },
            tf('It is acceptable to run a machine briefly with its guard removed if you are careful.', false, 'Guards must always be in place while the machine runs.'),
            tf('Guard openings should be small enough that fingers cannot reach the hazard.', true, 'Opening size is matched to reach distance.'),
            { prompt: 'Why should frequently removed guards be hinged?', answers: ['They look better', 'So they are not lost or left off', 'To reduce cost', 'To increase speed'], correct: 1, explanation: 'Detached guards tend to get misplaced and never reinstalled.' },
          ],
        },
      },
      {
        title: 'Controls and Safety Signs',
        description: 'Controls that prevent mistakes and signs that communicate risk.',
        lessons: [
          {
            title: 'Designing safe controls',
            minutes: 7,
            content: `Controls should make the right action easy and the wrong action hard.

- **Clearly marked** — each lever and switch is labelled with its function and direction.
- **Protected from accidental use** — starting controls are recessed or guarded so they can't be bumped.
- **Emergency stop** — easy to reach, red on a yellow background, and stops all hazardous motion.
- **Neutral start** — the engine should only start with the transmission in neutral or the PTO disengaged.`,
          },
          {
            title: 'Reading safety signs',
            minutes: 5,
            content: `Safety signs use a **signal word** to show how serious a hazard is:

- **DANGER** — will cause death or serious injury if not avoided.
- **WARNING** — could cause death or serious injury.
- **CAUTION** — may cause minor or moderate injury.

A good sign has a pictogram, the hazard, the consequence, and how to avoid it. Replace signs that are faded or missing — they are part of the machine.`,
          },
        ],
        quiz: {
          title: 'Controls & Signs Check',
          description: 'All questions. You need 60% to pass.',
          question_limit: null,
          passing_percent: 60,
          time_limit_minutes: null,
          questions: [
            { prompt: 'Which signal word indicates the most severe hazard?', answers: ['CAUTION', 'NOTICE', 'WARNING', 'DANGER'], correct: 3, explanation: 'DANGER means death or serious injury will result if not avoided.' },
            { prompt: 'A "neutral start" feature makes sure…', answers: ['The engine starts in any gear', 'The engine only starts with the drive disengaged', 'The lights turn on', 'Fuel is saved'], correct: 1, explanation: 'Starting in gear can make the machine lurch into people.' },
            tf('Faded safety signs should be replaced.', true, 'Signs are part of the machine and must stay readable.'),
            { prompt: 'Emergency stop controls are typically coloured…', answers: ['Green on white', 'Red on yellow', 'Blue on black', 'Any colour'], correct: 1, explanation: 'Red on a yellow background is the recognised convention.' },
          ],
        },
      },
    ],
  },
  {
    code: 'PAES 102',
    title: "Operator's Manual: Content and Presentation",
    description: "What every agricultural machine's operator's manual must contain, and how to write it so people actually use it.",
    color: 'sky',
    modules: [
      {
        title: 'What goes in a manual',
        description: 'The required sections of an operator’s manual.',
        lessons: [
          {
            title: 'Purpose of the operator’s manual',
            minutes: 5,
            content: `The operator's manual is the machine's instructions for use. It is supplied **with every machine** and should stay with it, even when the machine is resold.

It tells the operator:
1. What the machine is for — and what it is *not* for.
2. How to set it up, operate it and stop it safely.
3. How to maintain it and when.
4. What to do when something goes wrong.`,
          },
          {
            title: 'Typical sections',
            minutes: 8,
            content: `A complete manual usually includes:

- **Identification** — manufacturer, model, serial number location.
- **Safety** — the hazards, the meaning of each safety sign on the machine, protective equipment.
- **Specifications** — dimensions, weight, power requirement, capacity.
- **Assembly and installation**.
- **Operation** — controls, starting, adjusting, stopping.
- **Maintenance** — schedule, lubrication points, adjustments.
- **Troubleshooting** — problem → likely cause → remedy.
- **Parts list** and **warranty / service contacts**.`,
          },
        ],
        quiz: {
          title: 'Manual Contents Check',
          description: 'Four random questions. You need 75% to pass.',
          question_limit: 4,
          passing_percent: 75,
          time_limit_minutes: 8,
          questions: [
            tf('The operator’s manual should stay with the machine when it is sold.', true, 'The next owner needs it just as much.'),
            { prompt: 'Which section explains “problem → cause → remedy”?', answers: ['Specifications', 'Troubleshooting', 'Warranty', 'Assembly'], correct: 1, explanation: 'Troubleshooting tables map symptoms to fixes.' },
            { prompt: 'Where should the meaning of on-machine safety signs be explained?', answers: ['Parts list', 'Safety section', 'Warranty', 'Nowhere'], correct: 1, explanation: 'The safety section reproduces and explains each sign.' },
            { prompt: 'Which item belongs in the identification section?', answers: ['Lubrication points', 'Serial number location', 'Field speed', 'Spare parts prices'], correct: 1, explanation: 'Identification covers manufacturer, model and serial number.' },
            tf('A manual only needs to describe how to start the machine.', false, 'It covers setup, operation, stopping, maintenance and troubleshooting.'),
          ],
        },
      },
      {
        title: 'Writing clear instructions',
        description: 'Presentation rules that make manuals usable.',
        lessons: [
          {
            title: 'Write for the operator',
            minutes: 6,
            content: `Write for the person who will run the machine — often in the field, often in a hurry.

- Use the **local language** of the users, and plain words.
- One action per numbered step, in the order it is done.
- Put **warnings before** the step they apply to, not after.
- Use **pictures** for anything spatial: lubrication points, adjustments, belt routing.
- Use the same name for a part everywhere.`,
          },
        ],
      },
    ],
  },
  {
    code: 'FP 110',
    title: 'Farm Power and Tractor Basics',
    description: 'The main systems of a four-wheel tractor and how to operate one safely.',
    color: 'amber',
    modules: [
      {
        title: 'Tractor systems',
        description: 'Engine, power train, hitch and PTO.',
        lessons: [
          {
            title: 'Main parts of a tractor',
            minutes: 7,
            content: `A tractor converts engine power into **drawbar pull**, **PTO power** and **hydraulic power**.

- **Engine** — usually diesel.
- **Clutch and transmission** — select speed and direction.
- **Differential and final drive** — deliver power to the wheels.
- **Three-point hitch** — attaches and lifts implements.
- **Power take-off (PTO)** — a splined shaft that drives implements such as rotavators and mowers.

*Tip for teachers: paste a YouTube link in the lesson editor to embed a video here.*`,
          },
          {
            title: 'Rollover protection',
            minutes: 5,
            content: `Overturns are the leading cause of tractor deaths. A **ROPS** (rollover protective structure) with a **seat belt** keeps the operator inside a protected zone.

- Always wear the seat belt when the ROPS is up.
- Drive across slopes slowly; avoid sharp turns at speed.
- Hitch loads only to the drawbar — never above the axle.`,
          },
        ],
        quiz: {
          title: 'Tractor Basics Check',
          description: 'All questions. You need 70% to pass.',
          question_limit: null,
          passing_percent: 70,
          time_limit_minutes: null,
          questions: [
            { prompt: 'What does PTO stand for?', answers: ['Power take-off', 'Pull to operate', 'Primary transmission output', 'Pressure tank outlet'], correct: 0, explanation: 'The PTO transfers engine power to implements.' },
            tf('A ROPS protects you only if you wear the seat belt.', true, 'Without the belt you can be thrown out of the protected zone.'),
            { prompt: 'Where should loads be hitched?', answers: ['Above the rear axle', 'To the drawbar', 'To the front axle', 'Anywhere secure'], correct: 1, explanation: 'Hitching high makes the tractor flip backwards.' },
          ],
        },
      },
    ],
  },
]

/** Creates the sample courses through any backend. */
export async function importSeed(api: Api, onProgress?: (msg: string) => void) {
  let position = 0
  for (const c of SEED) {
    onProgress?.(`Creating ${c.title}…`)
    const course = await api.saveCourse({ code: c.code, title: c.title, description: c.description, color: c.color, published: true, position: position++ })
    for (const [mi, m] of c.modules.entries()) {
      const mod = await api.saveModule({ course_id: course.id, title: m.title, description: m.description, position: mi })
      for (const [li, l] of m.lessons.entries()) {
        await api.saveLesson({ module_id: mod.id, title: l.title, content: l.content, minutes: l.minutes, video_url: l.video_url ?? '', attachments: [], position: li })
      }
      if (m.quiz) {
        const { questions, ...quiz } = m.quiz
        const saved = await api.saveQuiz({ ...quiz, module_id: mod.id, position: 0 })
        for (const [qi, q] of questions.entries()) {
          await api.saveQuestion({
            quiz_id: saved.id, prompt: q.prompt, kind: q.kind ?? 'multiple_choice', explanation: q.explanation, position: qi,
            choices: q.answers.map((text, i) => ({ text, is_correct: i === q.correct })),
          })
        }
      }
    }
  }
}
