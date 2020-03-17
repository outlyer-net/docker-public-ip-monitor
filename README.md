# docker-public-ip-monitor

Keep a record of your public IP address over time.
\
This is a multiarch image, with 32 and 64 bit support on PC and ARM.

[![Image Size](https://images.microbadger.com/badges/image/outlyernet/public-ip-monitor.svg)][microbadger]

### Information

* [Docker Hub][dockerhub]
* [Github][github]

## Set up

This image contains a script to check your public ip and provides a barebones view of the recorded IPs over time.
\
The image will not check the IP periodically on its own, to do that you'll have to use something like a `crontab`, e.g.:

1. Deploy the image
\
`$ docker run public_ip:/data --name public-ip-monitor outlyernet/public-ip-monitor`

2. Add a `crontab` entry to update the recorded IP, e.g. every 15 minutes:
\
`$ crontab -e`\
`...` \
`*/15 * * * * docker exec public-ip-monitor /update`\
`...`

## Services

The image contains a short list of known stable servers that will return your IP address (and nothing else) when accessed over HTTPS in the file `services.txt`, one per line. And you can add your own. If you know of any other well stablished server let me know.

By default the updater script will pick a random server out of the list on each update, though you can force it to pick a specfic one through the `USE_SERVICE` environment variable (see below).

## Environment variables

You can modify the behaviour of the update script with a couple environment variables:

* `USE_SERVICE`: Set to 0 to pick a random entry from `services.txt`, or to any other number to use the server corresponding to such line (i.e. 1 picks `icanhazip.com`)
* `UPDATE_TIMEOUT`: Timeout in seconds to retrieve the IP (accepts decimals)

## LGPL 3.0+ License

See the `LICENSE` file for the complete text of the GNU Lesser General
Public License.

Excerpt:

<blockquote>
This package is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 3 of the License, or (at your option) any later version.

This package is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
</blockquote>

[dockerhub]: https://hub.docker.com/r/outlyernet/public-ip-monitor/
[github]: https://github.com/outlyer-net/docker-public-ip-monitor
[microbadger]: https://microbadger.com/images/outlyernet/public-ip-monitor
