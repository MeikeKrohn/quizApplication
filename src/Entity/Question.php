<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $questionText;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Exam", inversedBy="questions")
     */
    private $exam;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question", orphanRemoval=true)
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    public function __construct()
    {
        $this->exam = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionText(): ?string
    {
        return $this->questionText;
    }

    public function setQuestionText(string $questionText): self
    {
        $this->questionText = $questionText;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Exam[]
     */
    public function getExam(): Collection
    {
        return $this->exam;
    }

    public function addExam(Exam $exam): self
    {
        if (!$this->exam->contains($exam)) {
            $this->exam[] = $exam;
        }

        return $this;
    }

    public function removeExam(Exam $exam): self
    {
        if ($this->exam->contains($exam)) {
            $this->exam->removeElement($exam);
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @Assert\IsTrue(message="The question has to have at least two answers.")
     */
    public function hasAnswers()
    {
        if (sizeof($this->getAnswers()) >= 2) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @Assert\IsTrue(message="The question has to have at least one true answer.")
     */
    public function hasTrueAnswer()
    {
        $hasTrueAnswer = false;

        if ($this->getAnswers() != null) {
            foreach ($this->getAnswers() as $answer) {
                if ($answer->getIsCorrect()) {
                    $hasTrueAnswer = true;
                }
            }
        }

        return $hasTrueAnswer;
    }

    /**
     * @Assert\IsTrue(message="You have the same answer twice.")
     */
    public function hasIndividualAnswers()
    {
        $hasIndividualAnswer = false;
        $answerTexts = [];

        foreach($this->getAnswers() as $answer) {
            array_push($answerTexts, strtolower($answer->getAnswerText()));
        }

        if(sizeof(array_unique($answerTexts)) == sizeof($answerTexts)) {
            $hasIndividualAnswer = true;
        }

        return $hasIndividualAnswer;
    }
}
