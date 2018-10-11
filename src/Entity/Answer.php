<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnswerRepository")
 */
class Answer
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
    private $answerText;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCorrect;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Question", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\UserExam", mappedBy="givenAnswers")
     */
    private $userExams;

    public function __construct()
    {
        $this->userExams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswerText(): ?string
    {
        return $this->answerText;
    }

    public function setAnswerText(string $answerText): self
    {
        $this->answerText = $answerText;

        return $this;
    }

    public function getIsCorrect(): ?bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): self
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return Collection|UserExam[]
     */
    public function getUserExams(): Collection
    {
        return $this->userExams;
    }

    public function addUserExam(UserExam $userExam): self
    {
        if (!$this->userExams->contains($userExam)) {
            $this->userExams[] = $userExam;
            $userExam->addGivenAnswer($this);
        }

        return $this;
    }

    public function removeUserExam(UserExam $userExam): self
    {
        if ($this->userExams->contains($userExam)) {
            $this->userExams->removeElement($userExam);
            $userExam->removeGivenAnswer($this);
        }

        return $this;
    }
}
