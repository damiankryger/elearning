<?php

namespace App\Courses\Domain;

enum ActivityAction: string
{
    case COMPLETE = 'complete';
    case INCOMPLETE = 'incomplete';
    case ENROLL = 'enroll';
    case START = 'start';
}
