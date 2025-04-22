<?php
namespace Mordheim;

enum FighterState: string
{
    case STANDING = 'standing';
    case KNOCKED_DOWN = 'knocked_down';
    case STUNNED = 'stunned';
    case OUT_OF_ACTION = 'out_of_action';
    case PANIC = 'panic';
}
