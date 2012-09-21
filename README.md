# Dungeon Generator

Made by Cyle Gage

### Whaaaat?

This is a very basic dungeon map generator, as well as a generic adventure generator. I made it because I didn't want to be the DM in a D&D game and neither did anyone else, so I figured I'd make the computer do it. This acts to create the adventure, the dungeon, with monsters, and an ending. It's a bit like the D&D board games, but only the board and preset adventure part.

It is not anywhere near as complicated as having a real person be the DM, of course. There's a ton of subtle nuance to being a DM that a computer can't really replicate (yet) but this generator is good for really simple adventures. The majority of the adventure-work is still done by players, this just provides the basic layout for the adventure.

NOTE: The monster list included that's hard-coded into generate.php is based on the bestiary available in the [D&D Next playtest](http://www.wizards.com/dnd/dndnext.aspx).

### Features

* Crafts a basic adventure with random dungeon name, story, and goal. Every adventure is unique!
* * Currently has two basic adventure area styles: dungeon or cave.
* * Currently has two basic adventure goals: "reach a certain room" or "capture an item and escape."
* * Currently allows you to input a "desired level difficulty", up to level 5.
* Builds a random dungeon layout with a variable or user-set number of rooms, max width, and max height. It's meant to be copied down onto tiled graph paper or mat as you play.
* * Builds rooms of random size and position, complete with the possibility of monsters (according to level), with randomized room descriptions.
* * Builds hallways to connect rooms. Doors to hallways can be locked and/or barred, which requires a skill check to get past.
* * There is always a START room and an END room. The END room contains whatever goal was determined in the story.
* * Error detection. If the END room isn't accessible in some fashion, it'll throw an error and tell you to refresh for a new dungeon.
* Dungeon experience unfolds as you play.
* * You start out only seeing the START room as well as any hallways connected to it.
* * Players tackle the problems in the room, then click on a hallway to try to go down it.
* * On success, layout reveals the new room, room info, any rooms directly adjacent to it, and any hallways that lead from it.
* * Keep going til you reach the end!

### Requirements

* PHP 5.3+
* Web browser (tested primarily in Chrome)
* Friends to play D&D with
* All the stuff you need to play D&D: dice, character sheets, etc

### How to use

* Put index.html and generate.php somewhere. (Macs have PHP and Apache web server built in, they just need to be enabled. Google "enable apache php mac" for more info.)
* Go to index.html in a browser, fill out the form, and play!
* Click on hallways to move through the dungeon. Read the room descriptions and monster lists as you go along.

### Features that need to be done

* Saving the level layout and state just in case somebody closes or refreshes the browser.
* More interesting rooms, with stuff inside the rooms. Right now they're just rectangular rooms with monsters + descriptions.
* More robust combat system. Right now you just have to fight the monsters when you enter a room.
* Loot system. Right now, for playing, I recommend using [this Treasure Generator](http://donjon.bin.sh/d20/treasure/) at the end of combat to determine loot.
* Secrets system. Do a perception check in a room to try to reveal secret passages and/or loot.
* Additional floors, stairways.
* More area themes, more adventure types.

### Other notes

* I left generator\_try1.php and generator\_try2.php in there because they were my first attempts at generators, they work very differently from the "final" one, and I may reuse some of the functionality from them.