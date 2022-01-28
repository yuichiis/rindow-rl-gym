<?php
namespace Rindow\RL\Gym\Core\Graphics;

/**
 *
 */
interface GL
{
    /* BeginMode */
    const GL_POINTS                       = 0x0000;
    const GL_LINES                        = 0x0001;
    const GL_LINE_LOOP                    = 0x0002;
    const GL_LINE_STRIP                   = 0x0003;
    const GL_TRIANGLES                    = 0x0004;
    //const GL_TRIANGLE_STRIP               = 0x0005;
    //const GL_TRIANGLE_FAN                 = 0x0006;
    const GL_QUADS                        = 0x0007;
    //const GL_QUAD_STRIP                   = 0x0008;
    const GL_POLYGON                      = 0x0009;

    /* GetTarget */
    const GL_LINE_STIPPLE                 = 0x0B24;
    const GL_BLEND                        = 0x0BE2;

    /* BlendingFactorDest */
    const GL_SRC_ALPHA                    = 0x0302;
    const GL_ONE_MINUS_SRC_ALPHA          = 0x0303;
}
