/**
 * Array move function
 * @param from
 * @param to
 */
Array.prototype.move = function(from: number, to: number) {
    this.splice(to, 0, this.splice(from, 1)[0]);
    return this;
};
