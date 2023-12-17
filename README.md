# 1vs1 Plugin for PocketMine

## Description:
The 1vs1 plugin is designed for Minecraft PE (MCPE) server owners who want to facilitate 1vs1 matches on their servers. Packed with features like a multi-arena system, automatic queue management, and statistics signs, this plugin enhances the 1vs1 experience for both players and server administrators.

## Features:
- **Multi Arenas System:** Set up multiple arenas on your server, allowing concurrent 1vs1 matches.
- **Auto Queue Management:** Automatically manage player queues, ensuring a seamless experience for participants.
- **Statistics Signs:** Place signs with "[1vs1]" on the first line to display 1vs1 statistics, including the number of active arenas and players in the queue. The signs refresh every 5 seconds.

## How to Use:
1. **Reference Arenas:**
   - Use the command `/refarena` at the center of your arena to reference it.
   - Players will spawn 5 blocks from the middle of the arena.
   - You can create an unlimited number of arenas, and their positions are saved in the `config.yml` file.

2. **Start a Duel:**
   - Players can initiate a duel with `/match`.
   - A countdown will commence, and once complete, the fight begins (limited to 2 players per arena).
   - Players are teleported to the arena, equipped with a sword, armor, and food, with all effects removed for a fair fight.
   - The duel lasts 3 minutes, and if there is no winner, players are teleported back to the spawn.

3. **Statistics Signs:**
   - Place a sign with "[1vs1]" on the first line to create a 1vs1 stats sign.
   - The sign displays the number of active arenas and players in the queue.
   - Signs refresh every 5 seconds.

## Technical Details:
- After a fight, players are teleported back to the spawn of the default server level.
- If a player quits during a fight, their opponent is declared the winner.
- Arena and 1vs1 sign positions are stored in the `config.yml` file.
- If a player quits during the match countdown, the match stops.

## Commands:
- `/match`: Join the 1vs1 queue.
- `/refarena`: Reference a new arena.

## Notes:
- You can modify messages in the plugin source; future updates will allow customization via a message config file.
- Spawn distance in an arena and match timers will be customizable in a future update.
- Any feedback or remarks are welcome for continuous improvement!

*Enjoy your 1vs1 matches on your MCPE server!*
