<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserExamRepository")
 */
class UserExam
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userExams")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Exam", inversedBy="userExams")
     * @ORM\JoinColumn(nullable=false)
     */
    private $exam;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $result;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Answer", inversedBy="userExams")
     */
    private $givenAnswers;

    public function __construct()
    {
        $this->givenAnswers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getExam(): ?Exam
    {
        return $this->exam;
    }

    public function setExam(?Exam $exam): self
    {
        $this->exam = $exam;

        return $this;
    }

    public function getResult(): ?int
    {
        return $this->result;
    }

    public function setResult(?int $result): self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getGivenAnswers(): Collection
    {
        return $this->givenAnswers;
    }

    public function addGivenAnswer(Answer $givenAnswer): self
    {
        if (!$this->givenAnswers->contains($givenAnswer)) {
            $this->givenAnswers[] = $givenAnswer;
        }

        return $this;
    }

    public function removeGivenAnswer(Answer $givenAnswer): self
    {
        if ($this->givenAnswers->contains($givenAnswer)) {
            $this->givenAnswers->removeElement($givenAnswer);
        }

        return $this;
    }
}
