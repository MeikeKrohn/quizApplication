<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExamRepository")
 */
class Exam
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
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="exams")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserExam", mappedBy="exam", orphanRemoval=true)
     */
    private $userExams;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Question", mappedBy="exam")
     */
    private $questions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="exams")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRandomExam;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
            $userExam->setExam($this);
        }

        return $this;
    }

    public function removeUserExam(UserExam $userExam): self
    {
        if ($this->userExams->contains($userExam)) {
            $this->userExams->removeElement($userExam);
            // set the owning side to null (unless already changed)
            if ($userExam->getExam() === $this) {
                $userExam->setExam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->addExam($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            $question->removeExam($this);
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

    public function getIsRandomExam(): ?bool
    {
        return $this->isRandomExam;
    }

    public function setIsRandomExam(bool $isRandomExam): self
    {
        $this->isRandomExam = $isRandomExam;

        return $this;
    }

    /**
     * @Assert\IsTrue(message="You don't have questions in this category yet.")
     */
    public function hasQuestionsAvailable()
    {
        $questionsAvailable = false;
        $questions = $this->getOwner()->getQuestions();

        if ($this->getIsRandomExam()) {
            foreach($questions as $question) {
                if($question->getCategory() == $this->getCategory()) {
                    $questionsAvailable = true;
                }
            }
        }

        return $questionsAvailable;
    }

}
