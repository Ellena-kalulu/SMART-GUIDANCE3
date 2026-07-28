<?php

namespace Database\Seeders;

use App\Models\{AssessmentQuestion, QuestionOption};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentQuestionSeeder extends Seeder
{
    /** All Malawi MSCE secondary school subjects */
    private function allSubjects(): array
    {
        return [
            ['text' => 'English',                 'category' => 'Arts'],
            ['text' => 'Mathematics',             'category' => 'Technology'],
            ['text' => 'Chichewa',                'category' => 'Arts'],
            ['text' => 'Biology',                 'category' => 'Health'],
            ['text' => 'Chemistry',               'category' => 'Health'],
            ['text' => 'Physics',                 'category' => 'Engineering'],
            ['text' => 'General Science',         'category' => 'Health'],
            ['text' => 'Agriculture',             'category' => 'Agriculture'],
            ['text' => 'Additional Mathematics',  'category' => 'Technology'],
            ['text' => 'Computer Studies',        'category' => 'Technology'],
            ['text' => 'Technical Drawing',       'category' => 'Engineering'],
            ['text' => 'Woodwork',                'category' => 'Engineering'],
            ['text' => 'Metalwork',               'category' => 'Engineering'],
            ['text' => 'History',                 'category' => 'Arts'],
            ['text' => 'Geography',               'category' => 'Arts'],
            ['text' => 'Bible Knowledge',         'category' => 'Arts'],
            ['text' => 'Social Studies',          'category' => 'Arts'],
            ['text' => 'Life Skills',             'category' => 'Arts'],
            ['text' => 'Creative Arts',           'category' => 'Arts'],
            ['text' => 'Art and Design',          'category' => 'Arts'],
            ['text' => 'Music',                   'category' => 'Arts'],
            ['text' => 'Home Economics',          'category' => 'Health'],
            ['text' => 'French',                  'category' => 'Arts'],
            ['text' => 'Business Studies',        'category' => 'Business'],
            ['text' => 'Accounting',              'category' => 'Business'],
        ];
    }

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('assessment_responses')->truncate();
        DB::table('assessment_attempts')->truncate();
        DB::table('question_options')->truncate();
        DB::table('assessment_questions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($this->questions() as $order => $data) {
            $question = AssessmentQuestion::create([
                'question_text' => $data['question_text'],
                'type'          => $data['type'],
                'category'      => $data['category'],
                'section'       => $data['section'],
                'scale_labels'  => isset($data['scale_labels']) ? json_encode($data['scale_labels']) : null,
                'order'         => $order + 1,
                'is_active'     => true,
            ]);

            if (in_array($data['type'], ['multiple_choice', 'checkbox'])) {
                foreach ($data['options'] as $optOrder => $option) {
                    QuestionOption::create([
                        'question_id'            => $question->id,
                        'option_text'            => $option['text'],
                        'mapped_career_category' => $option['category'] ?? null,
                        'order'                  => $optOrder + 1,
                    ]);
                }
            }
        }

        $this->command->info('✅ Self Assessment questions seeded: ' . AssessmentQuestion::count());
    }

    private function questions(): array
    {
        $subjects = $this->allSubjects();

        return [
            // ── SECTION A ──
            [
                'section'       => 'Section A — Things You Like',
                'question_text' => 'What do you like to do when you are free?',
                'type'          => 'multiple_choice',
                'category'      => 'interests',
                'options'       => [
                    ['text' => 'Using computers or phones',       'category' => 'Technology'],
                    ['text' => 'Helping sick people',             'category' => 'Health'],
                    ['text' => 'Building or fixing things',       'category' => 'Engineering'],
                    ['text' => 'Working with plants or animals',  'category' => 'Agriculture'],
                    ['text' => 'Reading, writing or debating',    'category' => 'Arts'],
                    ['text' => 'Selling or doing business',       'category' => 'Business'],
                ],
            ],
            [
                'section'       => 'Section A — Things You Like',
                'question_text' => 'Which school subjects do you enjoy? (Pick all that fit)',
                'type'          => 'checkbox',
                'category'      => 'interests',
                'options'       => $subjects,
            ],
            [
                'section'       => 'Section A — Things You Like',
                'question_text' => 'Tell us about your hobbies or clubs. (Optional)',
                'type'          => 'text',
                'category'      => 'interests',
                'options'       => [],
            ],
            [
                'section'       => 'Section A — Things You Like',
                'question_text' => 'If you could visit one place for a day, which would you pick?',
                'type'          => 'multiple_choice',
                'category'      => 'interests',
                'options'       => [
                    ['text' => 'A hospital or clinic',              'category' => 'Health'],
                    ['text' => 'A technology company',            'category' => 'Technology'],
                    ['text' => 'A farm or forest',                'category' => 'Agriculture'],
                    ['text' => 'A court, school or media house',  'category' => 'Arts'],
                    ['text' => 'A bank or business office',       'category' => 'Business'],
                    ['text' => 'A workshop or construction site', 'category' => 'Engineering'],
                ],
            ],
            [
                'section'       => 'Section A — Things You Like',
                'question_text' => 'How much do you like maths and numbers?',
                'type'          => 'scale',
                'category'      => 'interests',
                'scale_labels'  => [1 => 'Not at all', 2 => 'A little', 3 => 'Sometimes', 4 => 'Quite a lot', 5 => 'Very much'],
                'options'       => [],
            ],

            // ── SECTION B ──
            [
                'section'       => 'Section B — Things You Are Good At',
                'question_text' => 'Which of these are you best at?',
                'type'          => 'multiple_choice',
                'category'      => 'skills',
                'options'       => [
                    ['text' => 'Solving maths problems',           'category' => 'Technology'],
                    ['text' => 'Understanding how the body works', 'category' => 'Health'],
                    ['text' => 'Drawing or designing things',      'category' => 'Engineering'],
                    ['text' => 'Writing good essays',            'category' => 'Arts'],
                    ['text' => 'Planning and organising',          'category' => 'Business'],
                    ['text' => 'Working with nature',            'category' => 'Agriculture'],
                ],
            ],
            [
                'section'       => 'Section B — Things You Are Good At',
                'question_text' => 'How good are you at science (Biology, Chemistry, Physics)?',
                'type'          => 'scale',
                'category'      => 'skills',
                'scale_labels'  => [1 => 'Very weak', 2 => 'Weak', 3 => 'Average', 4 => 'Good', 5 => 'Very good'],
                'options'       => [],
            ],
            [
                'section'       => 'Section B — Things You Are Good At',
                'question_text' => 'When you have a hard problem, what do you do?',
                'type'          => 'multiple_choice',
                'category'      => 'skills',
                'options'       => [
                    ['text' => 'Think step by step using logic', 'category' => 'Technology'],
                    ['text' => 'Ask people for help',            'category' => 'Health'],
                    ['text' => 'Try to build or test something', 'category' => 'Engineering'],
                    ['text' => 'Read books or search answers',   'category' => 'Arts'],
                    ['text' => 'Make a plan and use what I have','category' => 'Business'],
                ],
            ],
            [
                'section'       => 'Section B — Things You Are Good At',
                'question_text' => 'How well do you work in a team?',
                'type'          => 'scale',
                'category'      => 'skills',
                'scale_labels'  => [1 => 'Alone only', 2 => 'Not well', 3 => 'Okay', 4 => 'Well', 5 => 'Very well'],
                'options'       => [],
            ],

            [
                'section'       => 'Section B — Things You Are Good At',
                'question_text' => 'What are you proud of at school? (Optional)',
                'type'          => 'text',
                'category'      => 'skills',
                'options'       => [],
            ],

            // ── SECTION C ──
            [
                'section'       => 'Section C — Your Future Plans',
                'question_text' => 'What is most important to you in a future job?',
                'type'          => 'multiple_choice',
                'category'      => 'goals',
                'options'       => [
                    ['text' => 'Helping people and saving lives',    'category' => 'Health'],
                    ['text' => 'Creating new technology',            'category' => 'Technology'],
                    ['text' => 'Earning good money',               'category' => 'Business'],
                    ['text' => 'Working outdoors with nature',     'category' => 'Agriculture'],
                    ['text' => 'Having a stable and secure job',   'category' => 'Arts'],
                    ['text' => 'Building roads, houses or machines','category' => 'Engineering'],
                ],
            ],
            [
                'section'       => 'Section C — Your Future Plans',
                'question_text' => 'Do you already have a dream job in mind?',
                'type'          => 'multiple_choice',
                'category'      => 'goals',
                'options'       => [
                    ['text' => 'Yes — doctor or nurse',        'category' => 'Health'],
                    ['text' => 'Yes — work with computers',    'category' => 'Technology'],
                    ['text' => 'Yes — engineer',               'category' => 'Engineering'],
                    ['text' => 'Yes — farmer or scientist',    'category' => 'Agriculture'],
                    ['text' => 'Yes — lawyer or teacher',      'category' => 'Arts'],
                    ['text' => 'Yes — run a business',         'category' => 'Business'],
                    ['text' => 'No — not sure yet',            'category' => 'Arts'],
                ],
            ],
            [
                'section'       => 'Section C — Your Future Plans',
                'question_text' => 'How sure are you about the career you want?',
                'type'          => 'scale',
                'category'      => 'goals',
                'scale_labels'  => [1 => 'Not sure', 2 => 'A little', 3 => 'Somewhat', 4 => 'Quite sure', 5 => 'Very sure'],
                'options'       => [],
            ],
            [
                'section'       => 'Section C — Your Future Plans',
                'question_text' => 'What job or career do you dream about? (Optional)',
                'type'          => 'text',
                'category'      => 'goals',
                'options'       => [],
            ],
            [
                'section'       => 'Section C — Your Future Plans',
                'question_text' => 'What kind of work would make you happy every day? (Optional)',
                'type'          => 'text',
                'category'      => 'goals',
                'options'       => [],
            ],
            [
                'section'       => 'Section C — Your Future Plans',
                'question_text' => 'What worries you most about choosing a career? (Optional)',
                'type'          => 'text',
                'category'      => 'goals',
                'options'       => [],
            ],
        ];
    }
}
