<?php

namespace App\Security\Voter;

use App\Entity\Question;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class QuestionVoter extends Voter
{
    public const QUESTION_EDIT = 'QUESTION_EDIT';
    //public const QUESTION_VIEW = 'QUESTION_VIEW';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::QUESTION_EDIT])
            && $subject instanceof \App\Entity\Question;
    }

    /**
     * @throws \Exception
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Allow admins to do anything
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (!$subject instanceof Question) {
            throw new \Exception('Wrong type somehow passed');
        }

        switch ($attribute) {
            case self::QUESTION_EDIT:
                return $user === $subject->getOwner();
        }

        // ... (check conditions and return true to grant permission) ...
        /*switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;
        }*/

        return false;
    }
}
