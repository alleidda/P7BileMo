<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomerVoter extends Voter
{
    public const SHOW = 'USER';
    public const DELETE = 'DELETEUSER';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::DELETE])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $customer = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$customer instanceof UserInterface) {
            return false;
        }

        // check conditions and return true to grant permission
        if (in_array($attribute, ['USER', 'DELETEUSER'])) {
            return $customer === $subject->getCustomer();
        }

        return false;
    }
}
